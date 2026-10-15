<?php

namespace App\Http\Controllers;

use App\Models\ComiAgenda;
use App\Models\ComiCitaAgenda;
use App\Models\ComiProyecto;
use App\Models\ComiSolicitudServicio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Throwable;

class ComiAgendaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | AGENDA COMI
    |--------------------------------------------------------------------------
    |
    | Política de acceso:
    | - Rol 1 (Súper Admin): todas las agendas activas.
    | - Rol 2 (Admin Cliente): todas las agendas activas de su empresa.
    | - Otros roles: únicamente agendas asignadas en comi_agenda_usuarios.
    |
    */

    public function index(Request $request)
    {
        $usuario = $request->user();

        if (!$this->esSuperAdmin($usuario) && !$usuario->id_empresa) {
            abort(403, 'Tu usuario no tiene una empresa asignada.');
        }

        $filtros = $request->validate([
            'proyecto' => ['nullable', 'integer'],
            'agenda' => ['nullable', 'integer'],
            'estado' => [
                'nullable',
                Rule::in([
                    'pendiente_aprobacion',
                    'confirmada',
                    'rechazada',
                    'cancelada',
                    'reprogramada',
                ]),
            ],
            'fecha' => ['nullable', 'date'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | AGENDAS DISPONIBLES SEGÚN ROL
        |--------------------------------------------------------------------------
        */

        $agendaIds = $this->agendasVisibles($usuario);
        $agendasGestionables = $this->agendasGestionables($usuario);

        $agendasQuery = ComiAgenda::query()
            ->with('proyecto')
            ->whereIn('id_agenda', $agendaIds)
            ->where('activo', true);

        if (!$this->esSuperAdmin($usuario)) {
            $agendasQuery->where('id_empresa', $usuario->id_empresa);
        }

        $agendas = $agendasQuery
            ->orderBy('nombre')
            ->get();

        $proyectoIds = $agendas
            ->pluck('id_proyecto')
            ->unique()
            ->values();

        $proyectosQuery = ComiProyecto::query()
            ->whereIn('id_proyecto', $proyectoIds)
            ->where('activo', true);

        if (!$this->esSuperAdmin($usuario)) {
            $proyectosQuery->where('id_empresa', $usuario->id_empresa);
        }

        $proyectos = $proyectosQuery
            ->orderBy('nombre')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RESUMEN
        |--------------------------------------------------------------------------
        |
        | La fuente de verdad es comi_citas_agenda.
        |
        */

        $consultaBase = DB::table('comi_citas_agenda as c')
            ->whereIn('c.id_agenda', $agendaIds);

        if (!$this->esSuperAdmin($usuario)) {
            $consultaBase->where('c.id_empresa', $usuario->id_empresa);
        }

        $resumen = [
            'total' => (clone $consultaBase)->count(),

            'pendientes' => (clone $consultaBase)
                ->where('c.estado', 'pendiente_aprobacion')
                ->count(),

            'confirmadas' => (clone $consultaBase)
                ->where('c.estado', 'confirmada')
                ->count(),

            'reprogramadas' => (clone $consultaBase)
                ->where('c.estado', 'reprogramada')
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | CONSULTA DE COMI
        |--------------------------------------------------------------------------
        |
        | Esta consulta replica la estructura compartida por el equipo COMI:
        | comi_citas_agenda es la cita principal y clientes, calificación y
        | citas_agenda únicamente complementan los datos mostrados.
        |
        */

        $consulta = DB::table('comi_citas_agenda as c')
            ->join(
                'comi_agendas as a',
                'a.id_agenda',
                '=',
                'c.id_agenda'
            )
            ->join(
                'comi_proyectos as p',
                'p.id_proyecto',
                '=',
                'c.id_proyecto'
            )
            ->join(
                'empresas as e',
                'e.id_empresa',
                '=',
                'c.id_empresa'
            )
            ->leftJoin(
                'clientes as cl',
                'cl.id_cliente',
                '=',
                'c.id_cliente'
            );

        /*
         * Algunas bases locales anteriores no tienen todavía todas las tablas
         * o nombres de columna de producción. Estas comprobaciones permiten
         * que la vista siga abriendo sin cambiar la consulta de producción.
         */

        $tieneCalificaciones =
            Schema::hasTable('comi_calificaciones_lead');

        $tieneAgendaLegacy =
            Schema::hasTable('citas_agenda');

        if ($tieneCalificaciones) {
            $consulta->leftJoin(
                'comi_calificaciones_lead as cal',
                'cal.id_conversacion',
                '=',
                'c.id_conversacion'
            );
        }

        if ($tieneAgendaLegacy) {
            $consulta->leftJoin(
                'citas_agenda as ca',
                'ca.conversation_id',
                '=',
                'c.id_conversacion'
            );
        }

        $empresaCliente = Schema::hasColumn(
            'clientes',
            'organizacion'
        )
            ? 'cl.organizacion'
            : (
                Schema::hasColumn('clientes', 'empresa')
                    ? 'cl.empresa'
                    : 'NULL'
            );

        $empresaLegacy = $tieneAgendaLegacy
            ? 'ca.nombre_empresa'
            : 'NULL';

        $nombreLegacy = $tieneAgendaLegacy
            ? 'ca.nombre_contacto'
            : 'NULL';

        $telefonoLegacy = $tieneAgendaLegacy
            ? 'ca.telefono'
            : 'NULL';

        $correoLegacy = $tieneAgendaLegacy
            ? 'ca.correo'
            : 'NULL';

        $objetivoCalificacion = $tieneCalificaciones
            ? 'cal.objetivo_principal'
            : 'NULL';

        $objetivoLegacy = $tieneAgendaLegacy
            ? 'ca.objetivo_reunion'
            : 'NULL';

        $consulta
            ->select([
                'c.id_cita',
                'c.id_empresa',
                'c.id_proyecto',
                'c.id_agenda',
                'c.id_cliente',
                'c.id_conversacion',
                'c.id_solicitud',
                'c.fecha_hora_inicio as fecha_hora',
                'c.estado',
                'c.motivo',
                'p.nombre as proyecto',
                'p.slug as proyecto_slug',
                'a.nombre as agenda',
                'a.slug as agenda_slug',
                'a.zona_horaria',
                'e.nombre as tenant_empresa',
            ])
            ->selectRaw(
                "COALESCE(
                    {$empresaCliente},
                    {$empresaLegacy},
                    'Sin empresa'
                ) AS empresa_prospecto"
            )
            ->selectRaw(
                "COALESCE(
                    NULLIF(
                        TRIM(
                            concat_ws(
                                ' ',
                                cl.nombre,
                                cl.apellido_paterno,
                                cl.apellido_materno
                            )
                        ),
                        ''
                    ),
                    {$nombreLegacy},
                    'Sin nombre'
                ) AS nombre_prospecto"
            )
            ->selectRaw(
                "COALESCE(
                    cl.telefono_principal,
                    {$telefonoLegacy}
                ) AS telefono"
            )
            ->selectRaw(
                "COALESCE(
                    cl.correo,
                    {$correoLegacy}
                ) AS correo"
            )
            ->selectRaw(
                "COALESCE(
                    c.motivo,
                    {$objetivoCalificacion},
                    {$objetivoLegacy},
                    'Sin objetivo registrado'
                ) AS objetivo"
            )
            ->whereIn('c.id_agenda', $agendaIds);

        /*
        |--------------------------------------------------------------------------
        | AISLAMIENTO POR EMPRESA
        |--------------------------------------------------------------------------
        */

        if (!$this->esSuperAdmin($usuario)) {
            $consulta->where(
                'c.id_empresa',
                $usuario->id_empresa
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */

        if (!empty($filtros['proyecto'])) {
            $consulta->where(
                'c.id_proyecto',
                $filtros['proyecto']
            );
        }

        if (!empty($filtros['agenda'])) {
            $consulta->where(
                'c.id_agenda',
                $filtros['agenda']
            );
        }

        if (!empty($filtros['estado'])) {
            $consulta->where(
                'c.estado',
                $filtros['estado']
            );
        }

        if (!empty($filtros['fecha'])) {
            $consulta->whereDate(
                'c.fecha_hora_inicio',
                $filtros['fecha']
            );
        }

        $citas = $consulta
            ->orderByDesc('c.fecha_hora_inicio')
            ->paginate(15)
            ->withQueryString();

        return view(
            'comi.agenda.index',
            compact(
                'proyectos',
                'agendas',
                'citas',
                'resumen',
                'filtros',
                'agendasGestionables'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR / RECHAZAR - MODELO NUEVO
    |--------------------------------------------------------------------------
    */

    public function cambiarEstado(Request $request, $id_cita)
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'estado' => [
                'required',
                Rule::in([
                    'confirmada',
                    'rechazada',
                ]),
            ],
        ]);

        $query = ComiCitaAgenda::query()
            ->with('agenda')
            ->where('id_cita', $id_cita);

        if (!$this->esSuperAdmin($usuario)) {
            $query->where('id_empresa', $usuario->id_empresa);
        }

        $cita = $query->firstOrFail();

        $this->verificarGestionAgenda(
            $usuario,
            $cita->id_agenda
        );

        if ($cita->estado !== 'pendiente_aprobacion') {
            return back()->with(
                'error',
                'Esta cita ya no está pendiente de aprobación.'
            );
        }

        if (!$cita->id_conversacion) {
            return back()->with(
                'error',
                'Esta cita no está asociada a una conversación de COMI.'
            );
        }

        if (!$cita->agenda) {
            return back()->with(
                'error',
                'No fue posible identificar la agenda.'
            );
        }

        try {
            $resultado = DB::selectOne(
                '
                    SELECT
                        fn_comi_agenda_cambiar_estado(
                            ?,
                            ?,
                            ?
                        ) AS resultado
                ',
                [
                    $cita->agenda->slug,
                    $cita->id_conversacion,
                    $datos['estado'],
                ]
            );
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'No fue posible actualizar la cita.'
            );
        }

        if (!$resultado || !$resultado->resultado) {
            return back()->with(
                'error',
                'La cita no pudo ser actualizada.'
            );
        }

        $mensaje = $datos['estado'] === 'confirmada'
            ? 'La cita fue confirmada correctamente.'
            : 'La cita fue rechazada correctamente.';

        return back()->with('success', $mensaje);
    }


    /*
    |--------------------------------------------------------------------------
    | REPROGRAMAR - MODELO NUEVO
    |--------------------------------------------------------------------------
    */

    public function reprogramarForm(Request $request, $id_cita)
    {
        $usuario = $request->user();

        $query = ComiCitaAgenda::query()
            ->with([
                'agenda',
                'proyecto',
                'cliente',
            ])
            ->where('id_cita', $id_cita);

        if (!$this->esSuperAdmin($usuario)) {
            $query->where('id_empresa', $usuario->id_empresa);
        }

        $cita = $query->firstOrFail();

        $this->verificarGestionAgenda(
            $usuario,
            $cita->id_agenda
        );

        if ($cita->estado !== 'pendiente_aprobacion') {
            return redirect()
                ->route('comi.agenda.index')
                ->with(
                    'error',
                    'Solo pueden reprogramarse citas pendientes.'
                );
        }

        if (!$cita->id_conversacion) {
            return redirect()
                ->route('comi.agenda.index')
                ->with(
                    'error',
                    'Esta cita no tiene una conversación asociada.'
                );
        }

        if (!$cita->agenda) {
            return redirect()
                ->route('comi.agenda.index')
                ->with(
                    'error',
                    'No fue posible identificar la agenda.'
                );
        }

        $zonaHoraria = $cita->agenda->zona_horaria
            ?: 'America/Mexico_City';

        try {
            $resultados = DB::select(
                '
                    SELECT slot
                    FROM fn_comi_agenda_slots(
                        ?,
                        now(),
                        10
                    )
                ',
                [
                    $cita->agenda->slug,
                ]
            );

            $slots = collect($resultados)->map(
                fn ($resultado) =>
                    Carbon::parse($resultado->slot)
                        ->timezone($zonaHoraria)
            );
        } catch (Throwable $e) {
            report($e);
            $slots = collect();
        }

        return view(
            'comi.agenda.reprogramar',
            compact(
                'cita',
                'slots',
                'zonaHoraria'
            )
        );
    }


    public function reprogramar(Request $request, $id_cita)
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'fecha_hora' => [
                'required',
                'date_format:Y-m-d\TH:i',
            ],
        ]);

        $query = ComiCitaAgenda::query()
            ->with('agenda')
            ->where('id_cita', $id_cita);

        if (!$this->esSuperAdmin($usuario)) {
            $query->where('id_empresa', $usuario->id_empresa);
        }

        $cita = $query->firstOrFail();

        $this->verificarGestionAgenda(
            $usuario,
            $cita->id_agenda
        );

        if ($cita->estado !== 'pendiente_aprobacion') {
            return back()->with(
                'error',
                'Solo pueden reprogramarse citas pendientes.'
            );
        }

        if (!$cita->id_conversacion) {
            return back()->with(
                'error',
                'La cita no tiene una conversación asociada.'
            );
        }

        if (!$cita->agenda) {
            return back()->with(
                'error',
                'No fue posible identificar la agenda.'
            );
        }

        $zonaHoraria = $cita->agenda->zona_horaria
            ?: 'America/Mexico_City';

        try {
            $fecha = Carbon::createFromFormat(
                'Y-m-d\TH:i',
                $datos['fecha_hora'],
                $zonaHoraria
            );

            $resultado = DB::selectOne(
                '
                    SELECT
                        fn_comi_agenda_actualizar_cita(
                            ?,
                            ?,
                            ?,
                            ?
                        ) AS resultado
                ',
                [
                    $cita->agenda->slug,
                    $cita->id_conversacion,
                    'fecha_hora_cita',
                    $fecha->toIso8601String(),
                ]
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No fue posible reprogramar la cita.'
                );
        }

        if (!$resultado || !$resultado->resultado) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'El horario seleccionado ya no está disponible.'
                );
        }

        return redirect()
            ->route('comi.agenda.index')
            ->with(
                'success',
                'La cita fue reprogramada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SOLICITUDES DE SERVICIO
    |--------------------------------------------------------------------------
    */

    public function solicitudesServicio(Request $request)
    {
        $usuario = $request->user();

        if (!$this->esSuperAdmin($usuario) && !$usuario->id_empresa) {
            abort(403);
        }

        $datos = $request->validate([
            'estado' => [
                'nullable',
                Rule::in([
                    'pendiente_revision',
                    'requiere_contacto',
                    'cita_programada',
                    'rechazada',
                    'cancelada',
                ]),
            ],
        ]);

        $agendaIds = $this->agendasVisibles($usuario);

        $agendasQuery = ComiAgenda::query()
            ->with('proyecto')
            ->whereIn('id_agenda', $agendaIds)
            ->where('activo', true)
            ->whereHas(
                'proyecto',
                fn ($query) =>
                    $query->where('tipo', 'servicio_tecnico')
            );

        if (!$this->esSuperAdmin($usuario)) {
            $agendasQuery->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        $agendasTecnicas = $agendasQuery->get();

        $proyectoIds = $agendasTecnicas
            ->pluck('id_proyecto')
            ->unique()
            ->values();

        $consulta = ComiSolicitudServicio::query()
            ->with([
                'proyecto',
                'citas',
            ])
            ->whereIn('id_proyecto', $proyectoIds);

        if (!$this->esSuperAdmin($usuario)) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        if (!empty($datos['estado'])) {
            $consulta->where(
                'estado',
                $datos['estado']
            );
        }

        $solicitudes = $consulta
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $gestionables = $this->agendasGestionables(
            $usuario
        );

        $agendasTecnicasGestionables =
            $agendasTecnicas
                ->filter(
                    fn ($agenda) =>
                        $gestionables->contains(
                            (int) $agenda->id_agenda
                        )
                )
                ->values();

        return view(
            'comi.servicio.index',
            compact(
                'solicitudes',
                'agendasTecnicasGestionables'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PROGRAMAR VISITA
    |--------------------------------------------------------------------------
    */

    public function programarVisitaForm(
        Request $request,
        $id_solicitud
    ) {
        $usuario = $request->user();

        $solicitudQuery =
            ComiSolicitudServicio::query()
                ->with('proyecto')
                ->where(
                    'id_solicitud',
                    $id_solicitud
                );

        if (!$this->esSuperAdmin($usuario)) {
            $solicitudQuery->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        $solicitud = $solicitudQuery->firstOrFail();

        $gestionables = $this->agendasGestionables(
            $usuario
        );

        $agendasQuery = ComiAgenda::query()
            ->where(
                'id_proyecto',
                $solicitud->id_proyecto
            )
            ->whereIn(
                'id_agenda',
                $gestionables
            )
            ->where(
                'activo',
                true
            );

        if (!$this->esSuperAdmin($usuario)) {
            $agendasQuery->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        $agendas = $agendasQuery
            ->orderBy('nombre')
            ->get();

        if ($agendas->isEmpty()) {
            abort(
                403,
                'No tienes permiso para gestionar la agenda técnica de este proyecto.'
            );
        }

        $agendaSeleccionada = null;

        if ($request->filled('agenda')) {
            $agendaSeleccionada =
                $agendas->firstWhere(
                    'id_agenda',
                    (int) $request->agenda
                );
        }

        if (!$agendaSeleccionada) {
            $agendaSeleccionada = $agendas->first();
        }

        $zonaHoraria =
            $agendaSeleccionada->zona_horaria
            ?: 'America/Mexico_City';

        try {
            $resultados = DB::select(
                '
                    SELECT slot
                    FROM fn_comi_agenda_slots(
                        ?,
                        now(),
                        10
                    )
                ',
                [
                    $agendaSeleccionada->slug,
                ]
            );

            $slots = collect($resultados)->map(
                fn ($resultado) =>
                    Carbon::parse($resultado->slot)
                        ->timezone($zonaHoraria)
            );
        } catch (Throwable $e) {
            report($e);
            $slots = collect();
        }

        return view(
            'comi.servicio.programar',
            compact(
                'solicitud',
                'agendas',
                'agendaSeleccionada',
                'slots',
                'zonaHoraria'
            )
        );
    }


    public function programarVisita(
        Request $request,
        $id_solicitud
    ) {
        $usuario = $request->user();

        $datos = $request->validate([
            'id_agenda' => [
                'required',
                'integer',
            ],
            'fecha_hora' => [
                'required',
                'date_format:Y-m-d\TH:i',
            ],
        ]);

        $solicitudQuery =
            ComiSolicitudServicio::query()
                ->where(
                    'id_solicitud',
                    $id_solicitud
                );

        if (!$this->esSuperAdmin($usuario)) {
            $solicitudQuery->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        $solicitud = $solicitudQuery->firstOrFail();

        $agendaQuery = ComiAgenda::query()
            ->where(
                'id_agenda',
                $datos['id_agenda']
            )
            ->where(
                'id_proyecto',
                $solicitud->id_proyecto
            )
            ->where(
                'activo',
                true
            );

        if (!$this->esSuperAdmin($usuario)) {
            $agendaQuery->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        $agenda = $agendaQuery->firstOrFail();

        $this->verificarGestionAgenda(
            $usuario,
            $agenda->id_agenda
        );

        $zonaHoraria =
            $agenda->zona_horaria
            ?: 'America/Mexico_City';

        try {
            $fecha = Carbon::createFromFormat(
                'Y-m-d\TH:i',
                $datos['fecha_hora'],
                $zonaHoraria
            );

            $resultado = DB::selectOne(
                '
                    SELECT
                        fn_comi_programar_visita_tecnica(
                            ?,
                            ?,
                            ?
                        ) AS id_cita
                ',
                [
                    $agenda->slug,
                    $solicitud->id_solicitud,
                    $fecha->toIso8601String(),
                ]
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No fue posible programar la visita técnica.'
                );
        }

        if (!$resultado || !$resultado->id_cita) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'No fue posible generar la cita técnica.'
                );
        }

        return redirect()
            ->route('comi.servicio.index')
            ->with(
                'success',
                'La visita técnica fue programada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | AGENDA LEGACY - SOLO RESPALDO
    |--------------------------------------------------------------------------
    */

    private function datosAgendaLegacy(
        Request $request,
        $usuario,
        bool $usarFallback = true
    ): array {
        $puedeVerLegacy =
            $usarFallback
            && $this->puedeUsarAgendaLegacy($usuario);

        $resumenLegacy = [
            'total' => 0,
            'pendientes' => 0,
            'confirmadas' => 0,
            'rechazadas' => 0,
        ];

        $citasLegacy = null;

        if (!$puedeVerLegacy) {
            return compact(
                'puedeVerLegacy',
                'resumenLegacy',
                'citasLegacy'
            );
        }

        $resumenLegacy = [
            'total' =>
                DB::table('citas_agenda')->count(),

            'pendientes' =>
                DB::table('citas_agenda')
                    ->where(
                        'estado',
                        'pendiente_aprobacion'
                    )
                    ->count(),

            'confirmadas' =>
                DB::table('citas_agenda')
                    ->where(
                        'estado',
                        'confirmada'
                    )
                    ->count(),

            'rechazadas' =>
                DB::table('citas_agenda')
                    ->where(
                        'estado',
                        'rechazada'
                    )
                    ->count(),
        ];

        $consulta = DB::table('citas_agenda');

        $estado = $request->input(
            'legacy_estado'
        );

        $estadosPermitidos = [
            'pendiente_aprobacion',
            'confirmada',
            'rechazada',
            'reprogramada',
        ];

        if (
            $estado
            && in_array(
                $estado,
                $estadosPermitidos,
                true
            )
        ) {
            $consulta->where(
                'estado',
                $estado
            );
        }

        $fecha = $request->input(
            'legacy_fecha'
        );

        if (
            $fecha
            && preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $fecha
            )
        ) {
            $consulta->whereDate(
                'fecha_hora_cita',
                $fecha
            );
        }

        $citasLegacy = $consulta
            ->orderByDesc(
                'fecha_hora_cita'
            )
            ->paginate(
                15,
                ['*'],
                'legacy_page'
            )
            ->withQueryString();

        return compact(
            'puedeVerLegacy',
            'resumenLegacy',
            'citasLegacy'
        );
    }


    private function puedeUsarAgendaLegacy(
        $usuario
    ): bool {
        if ($this->esSuperAdmin($usuario)) {
            return true;
        }

        if (!$this->esAdminCliente($usuario)) {
            return false;
        }

        if (!$usuario->id_empresa) {
            return false;
        }

        /*
         * Fuente de verdad: el proyecto nuevo interacom-ventas.
         * Evitamos depender de que empresas.slug o empresas.nombre
         * tenga exactamente el texto "interacom".
         */
        $idEmpresaInteracom =
            DB::table('comi_proyectos')
                ->where(
                    'slug',
                    'interacom-ventas'
                )
                ->where(
                    'activo',
                    true
                )
                ->value('id_empresa');

        return $idEmpresaInteracom
            && (int) $usuario->id_empresa
                ===
                (int) $idEmpresaInteracom;
    }


    private function autorizarAgendaLegacy(
        $usuario
    ): void {
        abort_unless(
            $this->puedeUsarAgendaLegacy(
                $usuario
            ),
            403,
            'No tienes acceso a la agenda de COMI.'
        );
    }


    public function cambiarEstadoLegacy(
        Request $request,
        $id
    ) {
        $usuario = $request->user();

        $this->autorizarAgendaLegacy(
            $usuario
        );

        $datos = $request->validate([
            'estado' => [
                'required',
                Rule::in([
                    'confirmada',
                    'rechazada',
                ]),
            ],
        ]);

        $cita = DB::table('citas_agenda')
            ->where('id', $id)
            ->first();

        abort_unless($cita, 404);

        if (
            $cita->estado
            !== 'pendiente_aprobacion'
        ) {
            return back()->with(
                'error',
                'La cita ya no está pendiente de aprobación.'
            );
        }

        try {
            if (
                $datos['estado']
                === 'confirmada'
            ) {
                $resultado = DB::selectOne(
                    '
                        SELECT
                            fn_confirmar_cita(
                                CAST(? AS UUID)
                            ) AS resultado
                    ',
                    [
                        $cita->conversation_id,
                    ]
                );
            } else {
                $resultado = DB::selectOne(
                    '
                        SELECT
                            fn_rechazar_cita(
                                CAST(? AS UUID)
                            ) AS resultado
                    ',
                    [
                        $cita->conversation_id,
                    ]
                );
            }
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'No fue posible actualizar la cita.'
            );
        }

        if (
            !$resultado
            || !$resultado->resultado
        ) {
            return back()->with(
                'error',
                'La cita no pudo actualizarse.'
            );
        }

        $mensaje =
            $datos['estado']
                === 'confirmada'
            ? 'La cita fue confirmada correctamente.'
            : 'La cita fue rechazada correctamente.';

        return back()->with(
            'success',
            $mensaje
        );
    }


    public function reprogramarLegacyForm(
        Request $request,
        $id
    ) {
        $usuario = $request->user();

        $this->autorizarAgendaLegacy(
            $usuario
        );

        $cita = DB::table('citas_agenda')
            ->where('id', $id)
            ->first();

        abort_unless($cita, 404);

        if (
            $cita->estado
            !== 'pendiente_aprobacion'
        ) {
            return redirect()
                ->route(
                    'comi.agenda.index'
                )
                ->with(
                    'error',
                    'Solo pueden reprogramarse citas pendientes.'
                );
        }

        try {
            $resultados = DB::select(
                '
                    SELECT slot
                    FROM fn_obtener_slots_disponibles(
                        NOW(),
                        10
                    )
                '
            );

            $slots = collect(
                $resultados
            )->map(
                fn ($resultado) =>
                    Carbon::parse(
                        $resultado->slot
                    )->timezone(
                        'America/Mexico_City'
                    )
            );
        } catch (Throwable $e) {
            report($e);
            $slots = collect();
        }

        return view(
            'comi.agenda.reprogramar-legacy',
            compact(
                'cita',
                'slots'
            )
        );
    }


    public function reprogramarLegacy(
        Request $request,
        $id
    ) {
        $usuario = $request->user();

        $this->autorizarAgendaLegacy(
            $usuario
        );

        $datos = $request->validate([
            'fecha_hora' => [
                'required',
                'date_format:Y-m-d\TH:i',
            ],
        ]);

        $cita = DB::table('citas_agenda')
            ->where('id', $id)
            ->first();

        abort_unless($cita, 404);

        if (
            $cita->estado
            !== 'pendiente_aprobacion'
        ) {
            return back()->with(
                'error',
                'Solo pueden reprogramarse citas pendientes.'
            );
        }

        try {
            $fecha = Carbon::createFromFormat(
                'Y-m-d\TH:i',
                $datos['fecha_hora'],
                'America/Mexico_City'
            );

            $resultado = DB::selectOne(
                '
                    SELECT
                        fn_actualizar_campo_cita(
                            CAST(? AS UUID),
                            ?,
                            ?
                        ) AS resultado
                ',
                [
                    $cita->conversation_id,
                    'fecha_hora_cita',
                    $fecha->toIso8601String(),
                ]
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No fue posible reprogramar la cita.'
                );
        }

        if (
            !$resultado
            || !$resultado->resultado
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'El horario seleccionado ya no está disponible.'
                );
        }

        return redirect()
            ->route(
                'comi.agenda.index'
            )
            ->with(
                'success',
                'La cita fue reprogramada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | PERMISOS
    |--------------------------------------------------------------------------
    */

    private function esSuperAdmin(
        $usuario
    ): bool {
        return (int) $usuario->id_rol === 1;
    }


    private function esAdminCliente(
        $usuario
    ): bool {
        return (int) $usuario->id_rol === 2;
    }


    private function agendasVisibles(
        $usuario
    ) {
        /*
         * Súper Admin: todas.
         */
        if ($this->esSuperAdmin($usuario)) {
            return ComiAgenda::query()
                ->where('activo', true)
                ->pluck('id_agenda')
                ->map(
                    fn ($id) => (int) $id
                );
        }

        /*
         * Admin Cliente: todas las de su empresa.
         */
        if ($this->esAdminCliente($usuario)) {
            return ComiAgenda::query()
                ->where(
                    'id_empresa',
                    $usuario->id_empresa
                )
                ->where(
                    'activo',
                    true
                )
                ->pluck('id_agenda')
                ->map(
                    fn ($id) => (int) $id
                );
        }

        /*
         * Operarios y demás roles: asignación explícita.
         */
        return DB::table(
            'comi_agenda_usuarios'
        )
            ->where(
                'id_empresa',
                $usuario->id_empresa
            )
            ->where(
                'id_usuario',
                $usuario->id_usuario
            )
            ->where(
                'puede_ver',
                true
            )
            ->pluck('id_agenda')
            ->map(
                fn ($id) => (int) $id
            );
    }


    private function agendasGestionables(
        $usuario
    ) {
        /*
         * Súper Admin y Admin Cliente pueden gestionar
         * todas las agendas que administran por rol.
         */
        if (
            $this->esSuperAdmin($usuario)
            || $this->esAdminCliente($usuario)
        ) {
            return $this->agendasVisibles(
                $usuario
            );
        }

        return DB::table(
            'comi_agenda_usuarios'
        )
            ->where(
                'id_empresa',
                $usuario->id_empresa
            )
            ->where(
                'id_usuario',
                $usuario->id_usuario
            )
            ->where(
                'puede_gestionar',
                true
            )
            ->pluck('id_agenda')
            ->map(
                fn ($id) => (int) $id
            );
    }


    private function verificarGestionAgenda(
        $usuario,
        $idAgenda
    ): void {
        /*
         * Súper Admin: acceso total.
         */
        if ($this->esSuperAdmin($usuario)) {
            return;
        }

        /*
         * Admin Cliente: cualquier agenda activa de su empresa.
         */
        if ($this->esAdminCliente($usuario)) {
            $puedeGestionar =
                ComiAgenda::query()
                    ->where(
                        'id_agenda',
                        $idAgenda
                    )
                    ->where(
                        'id_empresa',
                        $usuario->id_empresa
                    )
                    ->where(
                        'activo',
                        true
                    )
                    ->exists();

            abort_unless(
                $puedeGestionar,
                403,
                'No tienes permiso para gestionar esta agenda.'
            );

            return;
        }

        /*
         * Otros roles: permiso explícito.
         */
        $puedeGestionar =
            DB::table(
                'comi_agenda_usuarios'
            )
                ->where(
                    'id_empresa',
                    $usuario->id_empresa
                )
                ->where(
                    'id_agenda',
                    $idAgenda
                )
                ->where(
                    'id_usuario',
                    $usuario->id_usuario
                )
                ->where(
                    'puede_gestionar',
                    true
                )
                ->exists();

        abort_unless(
            $puedeGestionar,
            403,
            'No tienes permiso para gestionar esta agenda.'
        );
    }
}
