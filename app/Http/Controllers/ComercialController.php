<?php

namespace App\Http\Controllers;

use App\Support\Comercial;
use App\Support\CitasProspecto;
use App\Support\FichaProspecto;
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
        abort_unless(
            (int) $request->user()->id_rol === 1 || Comercial::asesor($request->user()),
            403
        );

        // Una sola fila de resumen por cliente: incluye solicitudes y citas con horario pasado.
        $proximasCitas = CitasProspecto::resumen();

        $solicitudesCita = DB::table('comi_interacciones as ci')
            ->select('ci.id_empresa', 'ci.id_cliente')
            ->selectRaw('BOOL_OR(ci.cita_solicitada) as cita_solicitada_comi')
            ->groupBy('ci.id_empresa', 'ci.id_cliente');

        $query = DB::table('comercial_prospectos as p')
            ->join('clientes as c', function ($join) {
                $join->on('c.id_cliente', '=', 'p.id_cliente')
                    ->on('c.id_empresa', '=', 'p.id_empresa_origen');
            })
            ->leftJoin('usuarios as a', 'a.id_usuario', '=', 'p.id_asesor')
            ->leftJoinSub($proximasCitas, 'pc', function ($join) {
                $join->on('pc.id_empresa', '=', 'p.id_empresa_origen')
                    ->on('pc.id_cliente', '=', 'p.id_cliente');
            })
            ->leftJoinSub($solicitudesCita, 'sc', function ($join) {
                $join->on('sc.id_empresa', '=', 'p.id_empresa_origen')
                    ->on('sc.id_cliente', '=', 'p.id_cliente');
            })
            ->where('p.id_empresa', Comercial::empresa())
            ->whereIn('p.id_empresa_origen', Comercial::empresasOrigen())
            ->whereNull('c.deleted_at');

        // El asesor comercial únicamente ve sus prospectos asignados.
        if ((int) $request->user()->id_rol !== 1) {
            $query->where('p.id_asesor', $request->user()->getKey());
        }

        return $query->select(
            'p.*',
            'c.nombre',
            'c.apellido_paterno',
            'c.apellido_materno',
            'c.telefono_secundario',
            'c.updated_at as contacto_actualizado_at',
            'c.organizacion',
            'c.correo',
            'c.telefono_principal',
            'a.nombre as asesor_nombre',
            'a.apellido_paterno as asesor_apellido',
            'pc.cita_fecha',
            'pc.cita_id',
            'pc.cita_fuente',
            'pc.cita_estado',
            'pc.cita_total',
            'pc.cita_pasada',
            'pc.cita_agenda',
            'pc.cita_asesor',
            DB::raw('COALESCE(sc.cita_solicitada_comi, FALSE) as cita_solicitada_comi')
        );
    }

    /**
     * Incorpora automáticamente prospectos detectados por COMI.
     * También incorpora clientes que tengan una cita comercial en comi_citas_agenda o citas_agenda,
     * aunque no exista una fila correspondiente en comi_interacciones.
     */
    private function sincronizarProspectosComi(): int
    {
        $empresa = Comercial::empresa();
        $origenes = Comercial::empresasOrigen();
        $marcadores = implode(',', array_fill(0, count($origenes), '?'));

        return DB::affectingStatement(
            "INSERT INTO comercial_prospectos
                (id_empresa, id_empresa_origen, id_cliente)
             SELECT ?, c.id_empresa, c.id_cliente
             FROM clientes c
             WHERE c.id_empresa IN ({$marcadores})
               AND c.deleted_at IS NULL
               AND (
                    EXISTS (
                        SELECT 1
                        FROM comi_interacciones i
                        WHERE i.id_empresa = c.id_empresa
                          AND i.id_cliente = c.id_cliente
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM comi_calificaciones_lead q
                        WHERE q.id_empresa = c.id_empresa
                          AND q.id_cliente = c.id_cliente
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM comi_citas_agenda nueva
                        JOIN comi_proyectos pr ON pr.id_proyecto = nueva.id_proyecto
                          AND pr.id_empresa = nueva.id_empresa AND pr.tipo = 'comercial' AND pr.activo = TRUE
                        JOIN comi_agendas ag ON ag.id_agenda = nueva.id_agenda
                          AND ag.id_empresa = nueva.id_empresa AND ag.activo = TRUE
                        LEFT JOIN conversaciones co ON co.id_conversacion = nueva.id_conversacion
                          AND co.id_empresa = nueva.id_empresa
                        WHERE nueva.id_empresa = c.id_empresa
                          AND COALESCE(nueva.id_cliente, co.id_cliente) = c.id_cliente
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM conversaciones co
                        INNER JOIN citas_agenda ca
                            ON ca.conversation_id = co.id_conversacion
                        WHERE co.id_empresa = c.id_empresa
                          AND co.id_cliente = c.id_cliente
                    )
               )
             ON CONFLICT (id_empresa, id_empresa_origen, id_cliente)
             DO NOTHING",
            array_merge([$empresa], $origenes)
        );
    }

    private function registro(Request $request, $id, bool $bloquear = false)
    {
        $query = $this->consulta($request)
            ->where('p.id', $id);

        if ($bloquear) {
            $query->lock('FOR UPDATE OF p');
        }

        return $query->first() ?? abort(404);
    }

    public function index(Request $request)
    {
        if ((int) $request->user()->id_rol === 1) {
            $this->sincronizarProspectosComi();
        }

        $datos = $request->validate([
            'buscar' => 'nullable|string|max:100',
            'estado' => [
                'nullable',
                Rule::in(array_keys(Comercial::ESTADOS)),
            ],
            'asesor' => 'nullable|string|max:30',
            'cita' => [
                'nullable',
                Rule::in([
                    'programada',
                    'solicitada',
                    'sin_cita',
                    'con_registro',
                    'historica',
                ]),
            ],
            'desde' => 'nullable|date_format:Y-m-d',
            'hasta' => 'nullable|date_format:Y-m-d',
        ]);

        $buscar = trim($datos['buscar'] ?? '');
        $estado = $datos['estado'] ?? '';
        $asesor = $datos['asesor'] ?? '';
        $cita = $datos['cita'] ?? '';
        $desde = $datos['desde'] ?? '';
        $hasta = $datos['hasta'] ?? '';

        $base = $this->consulta($request);

        $resumen = [
            'total' => (clone $base)->count(),
            'pendientes' => (clone $base)
                ->whereIn('p.estado', [
                    'nuevo',
                    'asignado',
                    'en_seguimiento',
                ])
                ->count(),
            'cotizaciones' => (clone $base)
                ->where('p.estado', 'cotizacion_enviada')
                ->count(),
        ];

        if ($buscar !== '') {
            $base->where(function ($q) use ($buscar) {
                $termino = '%' . mb_strtolower($buscar, 'UTF-8') . '%';

                $q->whereRaw(
                    "LOWER(CONCAT(c.nombre, ' ', c.apellido_paterno)) LIKE ?",
                    [$termino]
                )
                    ->orWhereRaw('LOWER(c.organizacion) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(c.correo) LIKE ?', [$termino])
                    ->orWhere('c.telefono_principal', 'like', $termino);
            });
        }

        if ($estado !== '') {
            $base->where('p.estado', $estado);
        }

        if ($asesor === 'sin_asignar') {
            $base->whereNull('p.id_asesor');
        } elseif ($asesor !== '' && ctype_digit($asesor)) {
            $base->where('p.id_asesor', (int) $asesor);
        }

        if ($cita === 'programada') {
            $base->where('pc.cita_estado', 'confirmada')->where('pc.cita_pasada', false);
        } elseif ($cita === 'solicitada') {
            $base->where(function ($q) {
                $q->where('pc.cita_estado', 'pendiente_aprobacion')
                    ->orWhere(function ($q) {
                        $q->whereNull('pc.cita_id')->where('sc.cita_solicitada_comi', true);
                    });
            });
        } elseif ($cita === 'con_registro') {
            $base->whereNotNull('pc.cita_id');
        } elseif ($cita === 'historica') {
            $base->where('pc.cita_pasada', true);
        } elseif ($cita === 'sin_cita') {
            $base->whereNull('pc.cita_id')->where(function ($q) {
                $q->whereNull('sc.cita_solicitada_comi')->orWhere('sc.cita_solicitada_comi', false);
            });
        }

        if ($desde !== '') {
            $fechaDesde = Carbon::createFromFormat(
                'Y-m-d',
                $desde,
                config('app.timezone')
            )->startOfDay();

            $base->where('p.created_at', '>=', $fechaDesde);
        }

        if ($hasta !== '') {
            $fechaHasta = Carbon::createFromFormat(
                'Y-m-d',
                $hasta,
                config('app.timezone')
            )->endOfDay();

            $base->where('p.created_at', '<=', $fechaHasta);
        }

        $asesores = collect();

        if ((int) $request->user()->id_rol === 1) {
            $asesores = Comercial::asesores()
                ->where('activo', true)
                ->orderBy('nombre')
                ->orderBy('apellido_paterno')
                ->get();
        }

        $prospectos = $base
            ->orderByDesc('p.created_at')
            ->orderByDesc('p.id')
            ->paginate(15)
            ->withQueryString();

        return view('comercial.index', compact(
            'prospectos',
            'buscar',
            'estado',
            'asesor',
            'cita',
            'desde',
            'hasta',
            'asesores',
            'resumen'
        ));
    }

    public function show(Request $request, $id)
    {
        $prospecto = $this->registro($request, $id);

        $interacciones = DB::table('comi_interacciones')
            ->where('id_empresa', $prospecto->id_empresa_origen)
            ->where('id_cliente', $prospecto->id_cliente)
            ->orderByDesc('fecha_hora_inicio')
            ->paginate(10, ['*'], 'interacciones');

        $calificacion = DB::table('comi_calificaciones_lead')
            ->where('id_empresa', $prospecto->id_empresa_origen)
            ->where('id_cliente', $prospecto->id_cliente)
            ->orderByDesc('id_calificacion')
            ->first();

        $ficha = FichaProspecto::datos($prospecto);
        $citasProspecto = CitasProspecto::registros($prospecto)
            ->orderByDesc('cita_fecha')->orderByDesc('cita_id')
            ->paginate(10, ['*'], 'citas');

        $historial = DB::table('comercial_seguimientos as s')
            ->join('usuarios as u', 'u.id_usuario', '=', 's.id_usuario')
            ->where('s.id_prospecto', $id)
            ->select(
                's.*',
                'u.nombre',
                'u.apellido_paterno'
            )
            ->orderByDesc('s.id')
            ->paginate(15, ['*'], 'historial');

        $asesores = collect();
        $empresas = collect();

        if ((int) $request->user()->id_rol === 1) {
            $asesores = Comercial::asesores()
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            $empresas = DB::table('empresas')
                ->where('id_empresa', '<>', Comercial::empresa())
                ->where('activo', true)
                ->orderBy('nombre')
                ->get([
                    'id_empresa',
                    'nombre',
                ]);
        }

        return view('comercial.show', compact(
            'prospecto',
            'interacciones',
            'calificacion',
            'ficha',
            'citasProspecto',
            'historial',
            'asesores',
            'empresas'
        ));
    }

    public function editarInformacion(Request $request, $id)
    {
        $prospecto = $this->registro($request, $id);
        $ficha = FichaProspecto::datos($prospecto);
        $version = FichaProspecto::version($ficha);
        return view('comercial.editar-informacion', compact('prospecto', 'ficha', 'version'));
    }

    public function actualizarInformacion(Request $request, $id)
    {
        $this->registro($request, $id); // Rechazar otros asesores antes de procesar el formulario.
        $datos = $request->validate([
            'version' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/'],
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'present|nullable|string|max:100',
            'organizacion' => 'present|nullable|string|max:150',
            'telefono_principal' => ['present', 'nullable', 'string', 'regex:/^\+?[0-9]{7,15}$/'],
            'telefono_secundario' => ['present', 'nullable', 'string', 'regex:/^\+?[0-9]{7,15}$/'],
            'correo' => ['present', 'nullable', 'email:rfc', 'max:150', 'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/u'],
            'giro' => 'present|nullable|string|max:150',
            'numero_colaboradores' => 'present|nullable|string|max:100',
            'canal_principal' => 'present|nullable|string|max:100',
            'volumen_estimado' => 'present|nullable|string|max:100',
            'presupuesto_aproximado' => 'present|nullable|string|max:100',
            'objetivo_principal' => 'present|nullable|string|max:5000',
        ]);
        $cambiado = DB::transaction(function () use ($request, $id, $datos) {
            $p = $this->registro($request, $id, true); // Revalidar asignación dentro de la transacción.
            $ficha = FichaProspecto::datos($p, true);
            if (!hash_equals(FichaProspecto::version($ficha), $datos['version'])) {
                throw ValidationException::withMessages([
                    'version' => 'La información cambió mientras editabas. Abre de nuevo Editar información para revisar los datos actuales antes de guardar.',
                ]);
            }
            $contacto = [];
            $necesidades = [];
            $etiquetas = [];
            foreach (FichaProspecto::CONTACTO as $campo => $etiqueta) {
                $contacto[$campo] = $datos[$campo];
                if (($ficha['cliente']->$campo ?? null) !== $datos[$campo]) {
                    $etiquetas[] = $etiqueta;
                }
            }
            foreach (FichaProspecto::NECESIDADES as $campo => $etiqueta) {
                $necesidades[$campo] = $datos[$campo];
                if ($ficha['necesidades'][$campo] !== $datos[$campo]) {
                    $etiquetas[] = $etiqueta;
                }
            }
            if (!$etiquetas) {
                return false;
            }
            $ahora = now();
            $cambioContacto = false;
            foreach ($contacto as $campo => $valor) {
                $cambioContacto = $cambioContacto || ($ficha['cliente']->$campo ?? null) !== $valor;
            }
            if ($cambioContacto) {
                DB::table('clientes')->where('id_empresa', $p->id_empresa_origen)
                    ->where('id_cliente', $p->id_cliente)->whereNull('deleted_at')
                    ->update(array_merge($contacto, ['updated_at' => $ahora]));
            }
            $guardado = array_merge($necesidades, ['id_editor' => $request->user()->getKey(), 'updated_at' => $ahora]);
            if ($ficha['manual']) {
                DB::table('comercial_fichas_prospecto')->where('id_prospecto', $p->id)->update($guardado);
            } else {
                DB::table('comercial_fichas_prospecto')->insert(array_merge($guardado, [
                    'id_prospecto' => $p->id, 'created_at' => $ahora,
                ]));
            }
            $this->bitacora($request, $p, 'actualizacion_datos',
                'Información revisada. Campos actualizados: '.implode(', ', $etiquetas).'.',
                $p->estado, $p->proximo_contacto);
            return true;
        });
        return redirect()->route('comercial.prospectos.show', $id)
            ->with('exito', $cambiado ? 'Información del prospecto actualizada.' : 'No hubo cambios en la información.');
    }

    private function bitacora(
        Request $request,
        $p,
        string $tipo,
        string $nota,
        string $estado,
        $fecha = null
    ): void {
        DB::table('comercial_seguimientos')->insert([
            'id_prospecto' => $p->id,
            'id_usuario' => $request->user()->getKey(),
            'tipo' => $tipo,
            'nota' => $nota,
            'estado' => $estado,
            'proximo_contacto' => $fecha,
            'created_at' => now(),
        ]);
    }

    public function asignar(Request $request, $id)
    {
        abort_unless((int) $request->user()->id_rol === 1, 403);

        $datos = $request->validate([
            'id_asesor' => 'required|integer',
        ]);

        DB::transaction(function () use ($request, $id, $datos) {
            $asesor = Comercial::asesores()
                ->where('id_usuario', $datos['id_asesor'])
                ->where('activo', true)
                ->lockForUpdate()
                ->first();

            abort_unless(
                $asesor,
                422,
                'Selecciona un asesor interno activo.'
            );

            $p = $this->registro($request, $id, true);

            if (in_array($p->estado, ['contratado', 'no_interesado'], true)) {
                throw ValidationException::withMessages([
                    'id_asesor' => 'Reabre el seguimiento antes de reasignar.',
                ]);
            }

            if ((int) $p->id_asesor === (int) $asesor->id_usuario) {
                return;
            }

            $estado = $p->estado === 'nuevo'
                ? 'asignado'
                : $p->estado;

            DB::table('comercial_prospectos')
                ->where('id', $id)
                ->update([
                    'id_asesor' => $asesor->id_usuario,
                    'estado' => $estado,
                    'updated_at' => now(),
                ]);

            $this->bitacora(
                $request,
                $p,
                'asignacion',
                'Asesor anterior: ' .
                    ($p->id_asesor ?? 'sin asignar') .
                    '. Nuevo asesor: ' .
                    $asesor->id_usuario .
                    ' · ' .
                    $asesor->nombre .
                    ' ' .
                    $asesor->apellido_paterno,
                $estado,
                $p->proximo_contacto
            );
        });

        return back()->with('exito', 'Asignación actualizada.');
    }

    public function seguimiento(Request $request, $id)
    {
        $datos = $request->validate([
            'tipo' => [
                'required',
                Rule::in([
                    'llamada_manual',
                    'correo',
                    'whatsapp',
                    'nota',
                ]),
            ],
            'nota' => 'required|string|max:5000',
            'estado' => [
                'required',
                Rule::in([
                    'en_seguimiento',
                    'cotizacion_enviada',
                    'no_interesado',
                ]),
            ],
            'proximo_contacto' => 'nullable|date_format:Y-m-d\TH:i|after:now',
        ]);

        DB::transaction(function () use ($request, $id, $datos) {
            $p = $this->registro($request, $id, true);

            abort_if(
                $p->estado === 'contratado',
                422,
                'Este prospecto ya está contratado.'
            );

            $fecha = !empty($datos['proximo_contacto'])
                && $datos['estado'] !== 'no_interesado'
                    ? Carbon::createFromFormat(
                        'Y-m-d\TH:i',
                        $datos['proximo_contacto'],
                        config('app.timezone')
                    )->toIso8601String()
                    : null;

            DB::table('comercial_prospectos')
                ->where('id', $id)
                ->update([
                    'estado' => $datos['estado'],
                    'proximo_contacto' => $fecha,
                    'updated_at' => now(),
                ]);

            $this->bitacora(
                $request,
                $p,
                $datos['tipo'],
                $datos['nota'],
                $datos['estado'],
                $fecha
            );
        });

        return back()->with('exito', 'Seguimiento registrado.');
    }

    public function contratar(Request $request, $id)
    {
        abort_unless((int) $request->user()->id_rol === 1, 403);

        $datos = $request->validate([
            'id_empresa_contratada' => 'required|integer',
            'nota' => 'required|string|max:5000',
        ]);

        DB::transaction(function () use ($request, $id, $datos) {
            $p = $this->registro($request, $id, true);

            abort_if(
                $p->estado === 'contratado',
                422,
                'El contrato ya fue registrado.'
            );

            $valida = DB::table('empresas')
                ->where('id_empresa', $datos['id_empresa_contratada'])
                ->where('id_empresa', '<>', Comercial::empresa())
                ->where('activo', true)
                ->exists();

            abort_unless(
                $valida,
                422,
                'Selecciona una empresa cliente activa.'
            );

            DB::table('comercial_prospectos')
                ->where('id', $id)
                ->update([
                    'estado' => 'contratado',
                    'id_empresa_contratada' => $datos['id_empresa_contratada'],
                    'proximo_contacto' => null,
                    'updated_at' => now(),
                ]);

            $this->bitacora(
                $request,
                $p,
                'contrato',
                'Empresa #' . $datos['id_empresa_contratada'] . ': ' . $datos['nota'],
                'contratado'
            );
        });

        return back()->with(
            'exito',
            'Contratación registrada y vinculada a la empresa.'
        );
    }

    public function agenda(Request $request)
    {
        return app(AgendaComercialController::class)->index($request);
    }

    public function equipo(Request $request)
    {
        abort_unless((int) $request->user()->id_rol === 1, 403);

        $asesores = Comercial::asesores()
            ->orderBy('nombre')
            ->paginate(15);

        $cargas = DB::table('comercial_prospectos')
            ->where('id_empresa', Comercial::empresa())
            ->whereIn('id_empresa_origen', Comercial::empresasOrigen())
            ->whereNotIn('estado', [
                'contratado',
                'no_interesado',
            ])
            ->selectRaw('id_asesor, count(*) as total')
            ->groupBy('id_asesor')
            ->pluck('total', 'id_asesor');

        $actividad = DB::table('comercial_seguimientos as s')
            ->join(
                'comercial_prospectos as p',
                'p.id',
                '=',
                's.id_prospecto'
            )
            ->where('p.id_empresa', Comercial::empresa())
            ->whereIn('p.id_empresa_origen', Comercial::empresasOrigen())
            ->selectRaw('s.id_usuario, MAX(s.created_at) as ultima')
            ->groupBy('s.id_usuario')
            ->pluck('ultima', 'id_usuario');

        return view('comercial.equipo', compact(
            'asesores',
            'cargas',
            'actividad'
        ));
    }

    public function crearAsesor(Request $request)
    {
        abort_unless((int) $request->user()->id_rol === 1, 403);

        if (is_string($request->input('correo'))) {
            $request->merge([
                'correo' => mb_strtolower(
                    trim($request->input('correo')),
                    'UTF-8'
                ),
            ]);
        }

        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'correo' => [
                'bail',
                'required',
                'string',
                'email:rfc',
                'max:150',
                function ($attr, $value, $fail) {
                    if (
                        DB::table('usuarios')
                            ->whereRaw('LOWER(correo) = ?', [$value])
                            ->whereNull('deleted_at')
                            ->exists()
                    ) {
                        $fail('El correo ya está registrado.');
                    }
                },
            ],
            'password' => 'required|string|min:12|max:128|confirmed',
        ]);

        DB::transaction(function () use ($datos) {
            DB::statement(
                'LOCK TABLE usuarios IN SHARE ROW EXCLUSIVE MODE'
            );

            if (
                DB::table('usuarios')
                    ->whereRaw('LOWER(correo) = ?', [$datos['correo']])
                    ->whereNull('deleted_at')
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'correo' => 'El correo ya está registrado.',
                ]);
            }

            DB::table('usuarios')->insert([
                'id_empresa' => Comercial::empresa(),
                'id_rol' => Comercial::rol(),
                'id_tipo_operario' => null,
                'nombre' => $datos['nombre'],
                'apellido_paterno' => $datos['apellido_paterno'],
                'correo' => $datos['correo'],
                'password_hash' => Hash::make($datos['password']),
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with(
            'exito',
            'Asesor creado. Comunícale sus credenciales por un canal privado.'
        );
    }

    public function estadoAsesor(Request $request, $id)
    {
        abort_unless((int) $request->user()->id_rol === 1, 403);

        $datos = $request->validate([
            'activo' => 'required|boolean',
        ]);

        DB::transaction(function () use ($id, $datos) {
            $asesor = Comercial::asesores()
                ->where('id_usuario', $id)
                ->lockForUpdate()
                ->first();

            abort_unless($asesor, 404);
            if (!$datos['activo'] && DB::table('comi_cita_asignaciones as a')
                ->join('comi_citas_agenda as c', 'c.id_cita', '=', 'a.id_cita')
                ->where('a.id_asesor', $id)->where('c.estado', 'confirmada')
                ->where('c.fecha_hora_inicio', '>=', now())->exists()) {
                throw ValidationException::withMessages(['equipo' => 'El asesor tiene citas confirmadas futuras. Resuelve esas citas antes de desactivarlo.']);
            }


            if (
                !(bool) $datos['activo']
                && DB::table('comercial_prospectos')
                    ->where('id_empresa', Comercial::empresa())
                    ->where('id_asesor', $id)
                    ->whereNotIn('estado', [
                        'contratado',
                        'no_interesado',
                    ])
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'equipo' => 'Reasigna sus prospectos abiertos antes de desactivar al asesor.',
                ]);
            }

            DB::table('usuarios')
                ->where('id_usuario', $id)
                ->update([
                    'activo' => (bool) $datos['activo'],
                    'updated_at' => now(),
                ]);
        });

        return back()->with('exito', 'Estado del asesor actualizado.');
    }
}
