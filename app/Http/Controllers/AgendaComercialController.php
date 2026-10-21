<?php

namespace App\Http\Controllers;

use App\Mail\CitaComiConfirmada;
use App\Support\AgendaInterna;
use App\Support\Comercial;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class AgendaComercialController extends Controller
{
    private function filtrada(Request $request)
    {
        $datos = $request->validate([
            'buscar' => 'nullable|string|max:100',
            'asesor' => 'nullable|integer|min:1',
        ]);
        $q = AgendaInterna::consulta($request->user())
            ->where('c.estado', 'confirmada')->whereNotNull('asig.id_asesor');
        if (!empty($datos['asesor'])) {
            $q->where('asig.id_asesor', $datos['asesor']);
        }
        $buscar = trim($datos['buscar'] ?? '');
        if ($buscar !== '') {
            $q->where(function ($q) use ($buscar) {
                $patron = '%'.$buscar.'%';
                $q->whereRaw("concat_ws(' ', cl.nombre, cl.apellido_paterno, cl.organizacion, cl.correo, cl.telefono_principal, anterior.nombre_contacto, anterior.nombre_empresa, c.motivo) ILIKE ?", [$patron]);
            });
        }
        return $q;
    }

    public function index(Request $request)
    {
        $base = $this->filtrada($request);
        $zona = 'America/Mexico_City';
        $inicio = Carbon::now($zona)->startOfDay()->utc();
        $fin = Carbon::now($zona)->endOfDay()->utc();
        $resumen = [
            'hoy' => (clone $base)->whereBetween('c.fecha_hora_inicio', [$inicio, $fin])->count(),
            'anteriores' => (clone $base)->where('c.fecha_hora_inicio', '<', now())->count(),
            'proximas' => (clone $base)->where('c.fecha_hora_inicio', '>=', now())->count(),
        ];
        $proxima = (clone $base)->where('c.fecha_hora_inicio', '>=', now())
            ->orderBy('c.fecha_hora_inicio')->orderBy('c.id_cita')->first();
        $citas = $base->orderByRaw('(c.fecha_hora_inicio < NOW()) ASC')
            ->orderBy('c.fecha_hora_inicio')->orderBy('c.id_cita')->paginate(20)->withQueryString();
        $gestor = AgendaInterna::gestor($request->user());
        $asesores = $gestor ? Comercial::asesores()->where('activo', true)->orderBy('nombre')->get() : collect();
        return view('comercial.agenda', compact('citas', 'proxima', 'resumen', 'zona', 'gestor', 'asesores'));
    }

    public function eventos(Request $request)
    {
        $rango = $request->validate(['start' => 'required|date', 'end' => 'required|date|after:start']);
        $inicio = Carbon::parse($rango['start'], 'America/Mexico_City')->utc();
        $fin = Carbon::parse($rango['end'], 'America/Mexico_City')->utc();
        abort_if($inicio->diffInDays($fin) > 62, 422, 'Consulta hasta 62 días a la vez.');
        $citas = $this->filtrada($request)->where('c.fecha_hora_inicio', '<', $fin)
            ->whereRaw("c.fecha_hora_inicio + make_interval(mins => ag.duracion_minutos) > ?", [$inicio])
            ->orderBy('c.fecha_hora_inicio')->get();
        return response()->json($citas->map(function ($cita) {
            // Fechas de pared en México + UTC en el calendario: no dependen de la zona del navegador.
            $fecha = Carbon::parse($cita->fecha_hora_inicio)->timezone('America/Mexico_City');
            return [
                'id' => $cita->id_cita,
                'title' => $cita->contacto_nombre.' · '.$cita->asesor_nombre,
                'start' => $fecha->format('Y-m-d\TH:i:s'),
                'end' => $fecha->copy()->addMinutes($cita->duracion_minutos)->format('Y-m-d\TH:i:s'),
                'extendedProps' => ['telefono' => $cita->telefono_contacto, 'motivo' => $cita->motivo],
            ];
        }));
    }

    public function confirmar(Request $request, $id)
    {
        abort_unless(AgendaInterna::gestor($request->user()), 403);
        $datos = $request->validate(['id_asesor' => 'required|integer|min:1']);
        DB::transaction(function () use ($request, $id, $datos) {
            // Serializa asignaciones por asesor para evitar dos reservas simultáneas.
            $asesor = Comercial::asesores()->where('activo', true)
                ->where('id_usuario', $datos['id_asesor'])->lockForUpdate()->first();
            abort_unless($asesor, 422, 'Selecciona un asesor interno activo.');
            $cita = AgendaInterna::consulta($request->user())->where('c.id_cita', $id)
                ->lock('FOR UPDATE OF c')->first();
            abort_unless($cita, 404);
            if (!in_array($cita->estado, ['pendiente_aprobacion', 'confirmada'], true)) {
                throw ValidationException::withMessages(['cita' => 'Esta cita ya no admite confirmación o asignación.']);
            }
            if ($cita->id_asesor) {
                throw ValidationException::withMessages(['cita' => 'La cita ya tiene asesor. Recarga la página para ver la asignación actual.']);
            }
            $inicio = Carbon::parse($cita->fecha_hora_inicio);
            $fin = $inicio->copy()->addMinutes($cita->duracion_minutos);
            $ocupado = DB::table('comi_citas_agenda as otra')
                ->join('comi_cita_asignaciones as a', 'a.id_cita', '=', 'otra.id_cita')
                ->join('comi_agendas as ag', 'ag.id_agenda', '=', 'otra.id_agenda')
                ->where('a.id_asesor', $asesor->id_usuario)->where('otra.estado', 'confirmada')
                ->where('otra.id_cita', '<>', $id)->where('otra.fecha_hora_inicio', '<', $fin)
                ->whereRaw('otra.fecha_hora_inicio + make_interval(mins => ag.duracion_minutos) > ?', [$inicio])->exists();
            if ($ocupado) {
                throw ValidationException::withMessages(['id_asesor' => 'El asesor ya tiene una cita que se cruza con este horario.']);
            }
            DB::table('comi_cita_asignaciones')->insert([
                'id_cita' => $id, 'id_asesor' => $asesor->id_usuario,
                'id_gestor' => $request->user()->getKey(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('comi_citas_agenda')->where('id_cita', $id)->update(['estado' => 'confirmada', 'updated_at' => now()]);
        });
        $enviado = $this->enviarCorreo($request, $id);
        return back()->with($enviado ? 'success' : 'error', $enviado
            ? 'Cita confirmada y asignada. El correo de confirmación fue enviado.'
            : 'La cita quedó confirmada y asignada, pero el correo no pudo enviarse. Revisa el correo del contacto y la configuración de envío; después usa Reintentar correo.');
    }

    public function reintentarCorreo(Request $request, $id)
    {
        abort_unless(AgendaInterna::gestor($request->user()), 403);
        $enviado = $this->enviarCorreo($request, $id);
        return back()->with($enviado ? 'success' : 'error', $enviado
            ? 'La confirmación ya fue enviada al servidor de correo.'
            : 'No fue posible enviar el correo. La cita continúa confirmada y asignada.');
    }

    private function enviarCorreo(Request $request, $id): bool
    {
        // Se ejecuta después de confirmar. Un fallo SMTP no revierte ni duplica la cita.
        return DB::transaction(function () use ($request, $id) {
            $cita = AgendaInterna::consulta($request->user())->where('c.id_cita', $id)->first();
            abort_unless($cita && $cita->estado === 'confirmada' && $cita->id_asesor, 404);
            $asignacion = DB::table('comi_cita_asignaciones')->where('id_cita', $id)->lockForUpdate()->first();
            if ($asignacion->correo_enviado_at) {
                return true;
            }
            try {
                if (!filter_var($cita->correo_contacto, FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException('El contacto no tiene un correo válido.');
                }
                $mailer = config('mail.default');
                if (in_array(config("mail.mailers.$mailer.transport"), ['log', 'array'], true)) {
                    throw new \RuntimeException('Configura un transporte de correo real antes de enviar.');
                }
                Mail::to($cita->correo_contacto)->send(new CitaComiConfirmada([
                    'id' => $id, 'contacto' => $cita->contacto_nombre,
                    'asesor' => $cita->asesor_nombre, 'motivo' => $cita->motivo,
                    'fecha' => Carbon::parse($cita->fecha_hora_inicio)->timezone('America/Mexico_City')->format('d/m/Y H:i'),
                    'zona' => 'America/Mexico_City',
                ]));
                DB::table('comi_cita_asignaciones')->where('id_cita', $id)->update([
                    'correo_enviado_at' => now(), 'correo_destino' => $cita->correo_contacto,
                    'correo_error' => null, 'updated_at' => now(),
                ]);
                return true;
            } catch (Throwable $e) {
                report($e);
                DB::table('comi_cita_asignaciones')->where('id_cita', $id)->update([
                    'correo_error' => 'Envío pendiente. Revisar destinatario y configuración de correo.', 'updated_at' => now(),
                ]);
                return false;
            }
        });
    }
}
