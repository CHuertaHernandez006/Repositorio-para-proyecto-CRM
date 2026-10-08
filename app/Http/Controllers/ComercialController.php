<?php

namespace App\Http\Controllers;

use App\Support\Comercial;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ComercialController extends Controller
{
    private function consulta(Request $request)
    {
        abort_unless((int) $request->user()->id_rol === 1 || Comercial::asesor($request->user()), 403);
        $query = DB::table('comercial_prospectos as p')
            ->join('clientes as c', function ($join) {
                $join->on('c.id_cliente', '=', 'p.id_cliente')->on('c.id_empresa', '=', 'p.id_empresa_origen');
            })
            ->leftJoin('usuarios as a', 'a.id_usuario', '=', 'p.id_asesor')
            ->where('p.id_empresa', Comercial::empresa())
            ->whereIn('p.id_empresa_origen', Comercial::empresasOrigen())->whereNull('c.deleted_at');
        if ((int) $request->user()->id_rol !== 1) {
            $query->where('p.id_asesor', $request->user()->getKey());
        }
        return $query->select('p.*', 'c.nombre', 'c.apellido_paterno', 'c.organizacion',
            'c.correo', 'c.telefono_principal', 'a.nombre as asesor_nombre', 'a.apellido_paterno as asesor_apellido');
    }

    private function registro(Request $request, $id, bool $bloquear = false)
    {
        $query = $this->consulta($request)->where('p.id', $id);
        if ($bloquear) {
            $query->lock('FOR UPDATE OF p');
        }
        return $query->first() ?? abort(404);
    }

    public function index(Request $request)
    {
        $datos = $request->validate(['buscar' => 'nullable|string|max:100',
            'estado' => ['nullable', Rule::in(array_keys(Comercial::ESTADOS))]]);
        $buscar = trim($datos['buscar'] ?? '');
        $estado = $datos['estado'] ?? '';
        $base = $this->consulta($request);
        $resumen = ['total' => (clone $base)->count(),
            'pendientes' => (clone $base)->whereIn('p.estado', ['nuevo','asignado','en_seguimiento'])->count(),
            'cotizaciones' => (clone $base)->where('p.estado', 'cotizacion_enviada')->count()];
        if ($buscar !== '') {
            $base->where(function ($q) use ($buscar) {
                $termino = '%'.mb_strtolower($buscar, 'UTF-8').'%';
                $q->whereRaw("LOWER(CONCAT(c.nombre, ' ', c.apellido_paterno)) LIKE ?", [$termino])
                    ->orWhereRaw('LOWER(c.organizacion) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(c.correo) LIKE ?', [$termino])
                    ->orWhere('c.telefono_principal', 'like', $termino);
            });
        }
        if ($estado !== '') { $base->where('p.estado', $estado); }
        $prospectos = $base->orderByDesc('p.updated_at')->orderByDesc('p.id')->paginate(15)->withQueryString();
        return view('comercial.index', compact('prospectos','buscar','estado','resumen'));
    }

    public function incorporar(Request $request)
    {
        abort_unless((int) $request->user()->id_rol === 1, 403);
        $empresa = Comercial::empresa();
        $origenes = Comercial::empresasOrigen();
        $marcadores = implode(',', array_fill(0, count($origenes), '?'));

        // Conserva el cliente y sus datos en la empresa de origen.
        // Una calificación basta: COMI puede guardarla antes de la interacción.
        // EXISTS y ON CONFLICT evitan duplicados, incluso ante solicitudes simultáneas.
        $n = DB::affectingStatement("INSERT INTO comercial_prospectos
                (id_empresa, id_empresa_origen, id_cliente)
            SELECT ?, c.id_empresa, c.id_cliente FROM clientes c
            WHERE c.id_empresa IN ({$marcadores}) AND c.deleted_at IS NULL
            AND (
                EXISTS (SELECT 1 FROM comi_interacciones i
                    WHERE i.id_empresa = c.id_empresa AND i.id_cliente = c.id_cliente)
                OR EXISTS (SELECT 1 FROM comi_calificaciones_lead q
                    WHERE q.id_empresa = c.id_empresa AND q.id_cliente = c.id_cliente)
            )
            ON CONFLICT (id_empresa, id_empresa_origen, id_cliente) DO NOTHING",
            array_merge([$empresa], $origenes));

        return back()->with('exito', "Se incorporaron {$n} prospectos nuevos de COMI.");
    }

    public function show(Request $request, $id)
    {
        $prospecto = $this->registro($request, $id);
        $interacciones = DB::table('comi_interacciones')->where('id_empresa', $prospecto->id_empresa_origen)
            ->where('id_cliente', $prospecto->id_cliente)->orderByDesc('fecha_hora_inicio')->paginate(10, ['*'], 'interacciones');
        $calificacion = DB::table('comi_calificaciones_lead')->where('id_empresa', $prospecto->id_empresa_origen)
            ->where('id_cliente', $prospecto->id_cliente)->orderByDesc('id_calificacion')->first();
        $historial = DB::table('comercial_seguimientos as s')->join('usuarios as u', 'u.id_usuario', '=', 's.id_usuario')
            ->where('s.id_prospecto', $id)->select('s.*','u.nombre','u.apellido_paterno')
            ->orderByDesc('s.id')->paginate(15, ['*'], 'historial');
        $asesores = collect(); $empresas = collect();
        if ((int) $request->user()->id_rol === 1) {
            $asesores = Comercial::asesores()->where('activo', true)->orderBy('nombre')->get();
            $empresas = DB::table('empresas')->where('id_empresa', '<>', Comercial::empresa())->where('activo', true)->orderBy('nombre')->get(['id_empresa','nombre']);
        }
        return view('comercial.show', compact('prospecto','interacciones','calificacion','historial','asesores','empresas'));
    }

    private function bitacora(Request $request, $p, string $tipo, string $nota, string $estado, $fecha = null): void
    {
        DB::table('comercial_seguimientos')->insert(['id_prospecto' => $p->id,
            'id_usuario' => $request->user()->getKey(), 'tipo' => $tipo, 'nota' => $nota,
            'estado' => $estado, 'proximo_contacto' => $fecha, 'created_at' => now()]);
    }

    public function asignar(Request $request, $id)
    {
        abort_unless((int) $request->user()->id_rol === 1, 403);
        $datos = $request->validate(['id_asesor' => 'required|integer']);
        DB::transaction(function () use ($request, $id, $datos) {
            // Mismo orden de bloqueo que la desactivación: usuario, después prospecto.
            $asesor = Comercial::asesores()->where('id_usuario', $datos['id_asesor'])->where('activo', true)->lockForUpdate()->first();
            abort_unless($asesor, 422, 'Selecciona un asesor interno activo.');
            $p = $this->registro($request, $id, true);
            if (in_array($p->estado, ['contratado','no_interesado'], true)) {
                throw ValidationException::withMessages(['id_asesor' => 'Reabre el seguimiento antes de reasignar.']);
            }
            if ((int) $p->id_asesor === (int) $asesor->id_usuario) { return; }
            $estado = $p->estado === 'nuevo' ? 'asignado' : $p->estado;
            DB::table('comercial_prospectos')->where('id', $id)->update(['id_asesor' => $asesor->id_usuario, 'estado' => $estado, 'updated_at' => now()]);
            $this->bitacora($request, $p, 'asignacion', 'Asesor anterior: '.($p->id_asesor ?? 'sin asignar').'. Nuevo asesor: '.$asesor->id_usuario.' · '.$asesor->nombre.' '.$asesor->apellido_paterno, $estado, $p->proximo_contacto);
        });
        return back()->with('exito', 'Asignación actualizada.');
    }

    public function seguimiento(Request $request, $id)
    {
        $datos = $request->validate([
            'tipo' => ['required', Rule::in(['llamada_manual','correo','whatsapp','nota'])],
            'nota' => 'required|string|max:5000',
            'estado' => ['required', Rule::in(['en_seguimiento','cotizacion_enviada','no_interesado'])],
            'proximo_contacto' => 'nullable|date_format:Y-m-d\TH:i|after:now',
        ]);
        DB::transaction(function () use ($request, $id, $datos) {
            $p = $this->registro($request, $id, true);
            abort_if($p->estado === 'contratado', 422, 'Este prospecto ya está contratado.');
            $fecha = !empty($datos['proximo_contacto']) && $datos['estado'] !== 'no_interesado'
                ? Carbon::createFromFormat('Y-m-d\TH:i', $datos['proximo_contacto'], config('app.timezone'))->toIso8601String() : null;
            DB::table('comercial_prospectos')->where('id', $id)->update([
                'estado' => $datos['estado'], 'proximo_contacto' => $fecha, 'updated_at' => now()]);
            $this->bitacora($request, $p, $datos['tipo'], $datos['nota'], $datos['estado'], $fecha);
        });
        return back()->with('exito', 'Seguimiento registrado.');
    }

    public function contratar(Request $request, $id)
    {
        abort_unless((int) $request->user()->id_rol === 1, 403);
        $datos = $request->validate(['id_empresa_contratada' => 'required|integer', 'nota' => 'required|string|max:5000']);
        DB::transaction(function () use ($request, $id, $datos) {
            $p = $this->registro($request, $id, true);
            abort_if($p->estado === 'contratado', 422, 'El contrato ya fue registrado.');
            $valida = DB::table('empresas')->where('id_empresa', $datos['id_empresa_contratada'])
                ->where('id_empresa', '<>', Comercial::empresa())->where('activo', true)->exists();
            abort_unless($valida, 422, 'Selecciona una empresa cliente activa.');
            DB::table('comercial_prospectos')->where('id', $id)->update([
                'estado' => 'contratado', 'id_empresa_contratada' => $datos['id_empresa_contratada'],
                'proximo_contacto' => null, 'updated_at' => now()]);
            $this->bitacora($request, $p, 'contrato', 'Empresa #'.$datos['id_empresa_contratada'].': '.$datos['nota'], 'contratado');
        });
        return back()->with('exito', 'Contratación registrada y vinculada a la empresa.');
    }

    public function agenda(Request $request)
    {
        $prospectos = $this->consulta($request)->whereNotNull('p.proximo_contacto')
            ->whereNotIn('p.estado', ['contratado','no_interesado'])
            ->orderBy('p.proximo_contacto')->orderBy('p.id')->paginate(20);
        return view('comercial.agenda', compact('prospectos'));
    }

    public function equipo(Request $request)
    {
        abort_unless((int) $request->user()->id_rol === 1, 403);
        $asesores = Comercial::asesores()->orderBy('nombre')->paginate(15);
        $cargas = DB::table('comercial_prospectos')->where('id_empresa', Comercial::empresa())
            ->whereIn('id_empresa_origen', Comercial::empresasOrigen())
            ->whereNotIn('estado', ['contratado','no_interesado'])->selectRaw('id_asesor, count(*) as total')->groupBy('id_asesor')->pluck('total','id_asesor');
        $actividad = DB::table('comercial_seguimientos as s')->join('comercial_prospectos as p','p.id','=','s.id_prospecto')
            ->where('p.id_empresa', Comercial::empresa())
            ->whereIn('p.id_empresa_origen', Comercial::empresasOrigen())->selectRaw('s.id_usuario, MAX(s.created_at) as ultima')->groupBy('s.id_usuario')->pluck('ultima','id_usuario');
        return view('comercial.equipo', compact('asesores','cargas','actividad'));
    }

    public function crearAsesor(Request $request)
    {
        abort_unless((int) $request->user()->id_rol === 1, 403);
        if (is_string($request->input('correo'))) {
            $request->merge(['correo' => mb_strtolower(trim($request->input('correo')), 'UTF-8')]);
        }
        $datos = $request->validate(['nombre' => 'required|string|max:100', 'apellido_paterno' => 'required|string|max:100',
            'correo' => ['bail','required','string','email:rfc','max:150', function ($attr, $value, $fail) {
                if (DB::table('usuarios')->whereRaw('LOWER(correo) = ?', [$value])->whereNull('deleted_at')->exists()) {
                    $fail('El correo ya está registrado.');
                }
            }], 'password' => 'required|string|min:12|max:128|confirmed']);
        // Evita la carrera entre dos altas y la comprobación de correo.
        DB::transaction(function () use ($datos) {
            DB::statement('LOCK TABLE usuarios IN SHARE ROW EXCLUSIVE MODE');
            if (DB::table('usuarios')->whereRaw('LOWER(correo) = ?', [$datos['correo']])->whereNull('deleted_at')->exists()) {
                throw ValidationException::withMessages(['correo' => 'El correo ya está registrado.']);
            }
            DB::table('usuarios')->insert(['id_empresa' => Comercial::empresa(), 'id_rol' => Comercial::rol(),
                'id_tipo_operario' => null, 'nombre' => $datos['nombre'], 'apellido_paterno' => $datos['apellido_paterno'],
                'correo' => $datos['correo'], 'password_hash' => Hash::make($datos['password']), 'activo' => true,
                'created_at' => now(), 'updated_at' => now()]);
        });
        return back()->with('exito', 'Asesor creado. Comunícale sus credenciales por un canal privado.');
    }

    public function estadoAsesor(Request $request, $id)
    {
        abort_unless((int) $request->user()->id_rol === 1, 403);
        $datos = $request->validate(['activo' => 'required|boolean']);
        DB::transaction(function () use ($id, $datos) {
            $asesor = Comercial::asesores()->where('id_usuario', $id)->lockForUpdate()->first();
            abort_unless($asesor, 404);
            if (!(bool) $datos['activo'] && DB::table('comercial_prospectos')->where('id_empresa', Comercial::empresa())
                ->where('id_asesor', $id)->whereNotIn('estado', ['contratado','no_interesado'])->exists()) {
                throw ValidationException::withMessages(['equipo' => 'Reasigna sus prospectos abiertos antes de desactivar al asesor.']);
            }
            DB::table('usuarios')->where('id_usuario', $id)->update(['activo' => (bool) $datos['activo'], 'updated_at' => now()]);
        });
        return back()->with('exito', 'Estado del asesor actualizado.');
    }
}
