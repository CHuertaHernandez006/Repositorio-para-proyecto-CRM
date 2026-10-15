<?php


namespace App\Http\Controllers;


use App\Models\ComiAgenda;

use App\Models\ComiCitaAgenda;

use App\Models\ComiProyecto;

use App\Models\ComiSolicitudServicio;

use Carbon\Carbon;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Validation\Rule;

use Throwable;


class ComiAgendaController extends Controller

{


    public function index(Request $request)

    {

        $usuario = $request->user();


        if (!$usuario->id_empresa) {

            abort(

                403,

                'Tu usuario no tiene una empresa asignada.'

            );

        }


        $filtros = $request->validate([

            'proyecto' => [

                'nullable',

                'integer',

            ],


            'agenda' => [

                'nullable',

                'integer',

            ],


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


            'fecha' => [

                'nullable',

                'date',

            ],

        ]);


        $agendaIds = $this->agendasVisibles(

            $usuario

        );


        $agendasGestionables = $this

            ->agendasGestionables(

                $usuario

            );


        $agendas = ComiAgenda::query()

            ->with('proyecto')

            ->where(

                'id_empresa',

                $usuario->id_empresa

            )

            ->whereIn(

                'id_agenda',

                $agendaIds

            )

            ->where(

                'activo',

                true

            )

            ->orderBy('nombre')

            ->get();


        $proyectoIds = $agendas

            ->pluck('id_proyecto')

            ->unique()

            ->values();


        $proyectos = ComiProyecto::query()

            ->where(

                'id_empresa',

                $usuario->id_empresa

            )

            ->whereIn(

                'id_proyecto',

                $proyectoIds

            )

            ->where(

                'activo',

                true

            )

            ->orderBy('nombre')

            ->get();


        $consultaBase = ComiCitaAgenda::query()

            ->where(

                'id_empresa',

                $usuario->id_empresa

            )

            ->whereIn(

                'id_agenda',

                $agendaIds

            );


        $resumen = [

            'total' =>

                (clone $consultaBase)

                    ->count(),


            'pendientes' =>

                (clone $consultaBase)

                    ->where(

                        'estado',

                        'pendiente_aprobacion'

                    )

                    ->count(),


            'confirmadas' =>

                (clone $consultaBase)

                    ->where(

                        'estado',

                        'confirmada'

                    )

                    ->count(),


            'reprogramadas' =>

                (clone $consultaBase)

                    ->where(

                        'estado',

                        'reprogramada'

                    )

                    ->count(),

        ];


        $consulta = ComiCitaAgenda::query()

            ->with([

                'proyecto',

                'agenda',

                'cliente',

                'solicitudServicio',

            ])

            ->where(

                'id_empresa',

                $usuario->id_empresa

            )

            ->whereIn(

                'id_agenda',

                $agendaIds

            );


        if (!empty($filtros['proyecto'])) {

            $consulta->where(

                'id_proyecto',

                $filtros['proyecto']

            );

        }


        if (!empty($filtros['agenda'])) {

            $consulta->where(

                'id_agenda',

                $filtros['agenda']

            );

        }


        if (!empty($filtros['estado'])) {

            $consulta->where(

                'estado',

                $filtros['estado']

            );

        }


        if (!empty($filtros['fecha'])) {

            $consulta->whereDate(

                'fecha_hora_inicio',

                $filtros['fecha']

            );

        }


        $citas = $consulta

            ->orderBy(

                'fecha_hora_inicio'

            )

            ->paginate(15)

            ->withQueryString();


        $legacy = $this->datosAgendaLegacy(
            $request,
            $usuario
        );


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

        )->with($legacy);

    }


    public function cambiarEstado(

        Request $request,

        $id_cita

    ) {

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


        $cita = ComiCitaAgenda::query()

            ->with('agenda')

            ->where(

                'id_cita',

                $id_cita

            )

            ->where(

                'id_empresa',

                $usuario->id_empresa

            )

            ->firstOrFail();


        $this->verificarGestionAgenda(

            $usuario,

            $cita->id_agenda

        );


        if (

            $cita->estado !==

            'pendiente_aprobacion'

        ) {

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


        if (

            !$resultado

            ||

            !$resultado->resultado

        ) {

            return back()->with(

                'error',

                'La cita no pudo ser actualizada.'

            );

        }


        $mensaje =

            $datos['estado'] === 'confirmada'

                ? 'La cita fue confirmada correctamente.'

                : 'La cita fue rechazada correctamente.';


        return back()->with(

            'success',

            $mensaje

        );

    }


    public function reprogramarForm(

        Request $request,

        $id_cita

    ) {

        $usuario = $request->user();


        $cita = ComiCitaAgenda::query()

            ->with([

                'agenda',

                'proyecto',

                'cliente',

            ])

            ->where(

                'id_cita',

                $id_cita

            )

            ->where(

                'id_empresa',

                $usuario->id_empresa

            )

            ->firstOrFail();


        $this->verificarGestionAgenda(

            $usuario,

            $cita->id_agenda

        );


        if (

            $cita->estado !==

            'pendiente_aprobacion'

        ) {

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


        $zonaHoraria =

            $cita->agenda->zona_horaria

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


            $slots = collect(

                $resultados

            )->map(

                function ($resultado)

                use ($zonaHoraria) {


                    return Carbon::parse(

                        $resultado->slot

                    )->timezone(

                        $zonaHoraria

                    );

                }

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


    public function reprogramar(

        Request $request,

        $id_cita

    ) {

        $usuario = $request->user();


        $datos = $request->validate([

            'fecha_hora' => [

                'required',

                'date_format:Y-m-d\TH:i',

            ],

        ]);


        $cita = ComiCitaAgenda::query()

            ->with('agenda')

            ->where(

                'id_cita',

                $id_cita

            )

            ->where(

                'id_empresa',

                $usuario->id_empresa

            )

            ->firstOrFail();


        $this->verificarGestionAgenda(

            $usuario,

            $cita->id_agenda

        );


        if (

            $cita->estado !==

            'pendiente_aprobacion'

        ) {

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


        $zonaHoraria =

            $cita->agenda->zona_horaria

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


        if (

            !$resultado

            ||

            !$resultado->resultado

        ) {

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


    public function solicitudesServicio(

        Request $request

    ) {

        $usuario = $request->user();


        if (!$usuario->id_empresa) {

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


        $agendaIds =

            $this->agendasVisibles(

                $usuario

            );


        $agendasTecnicas =

            ComiAgenda::query()

                ->with('proyecto')

                ->where(

                    'id_empresa',

                    $usuario->id_empresa

                )

                ->whereIn(

                    'id_agenda',

                    $agendaIds

                )

                ->where(

                    'activo',

                    true

                )

                ->whereHas(

                    'proyecto',

                    function ($query) {


                        $query->where(

                            'tipo',

                            'servicio_tecnico'

                        );

                    }

                )

                ->get();


        $proyectoIds =

            $agendasTecnicas

                ->pluck('id_proyecto')

                ->unique()

                ->values();


        $consulta =

            ComiSolicitudServicio::query()

                ->with([

                    'proyecto',

                    'citas',

                ])

                ->where(

                    'id_empresa',

                    $usuario->id_empresa

                )

                ->whereIn(

                    'id_proyecto',

                    $proyectoIds

                );


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


        $gestionables =

            $this->agendasGestionables(

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


    public function programarVisitaForm(

        Request $request,

        $id_solicitud

    ) {

        $usuario = $request->user();


        $solicitud =

            ComiSolicitudServicio::query()

                ->with('proyecto')

                ->where(

                    'id_solicitud',

                    $id_solicitud

                )

                ->where(

                    'id_empresa',

                    $usuario->id_empresa

                )

                ->firstOrFail();


        $gestionables =

            $this->agendasGestionables(

                $usuario

            );


        $agendas =

            ComiAgenda::query()

                ->where(

                    'id_empresa',

                    $usuario->id_empresa

                )

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

                )

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

            $agendaSeleccionada =

                $agendas->first();

        }


        $zonaHoraria =

            $agendaSeleccionada

                ->zona_horaria

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


            $slots = collect(

                $resultados

            )->map(

                fn ($resultado) =>

                    Carbon::parse(

                        $resultado->slot

                    )->timezone(

                        $zonaHoraria

                    )

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


        $solicitud =

            ComiSolicitudServicio::query()

                ->where(

                    'id_solicitud',

                    $id_solicitud

                )

                ->where(

                    'id_empresa',

                    $usuario->id_empresa

                )

                ->firstOrFail();


        $agenda = ComiAgenda::query()

            ->where(

                'id_agenda',

                $datos['id_agenda']

            )

            ->where(

                'id_empresa',

                $usuario->id_empresa

            )

            ->where(

                'id_proyecto',

                $solicitud->id_proyecto

            )

            ->where(

                'activo',

                true

            )

            ->firstOrFail();


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


        if (

            !$resultado

            ||

            !$resultado->id_cita

        ) {

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
    | AGENDA LEGADA DE COMI
    |--------------------------------------------------------------------------
    |
    | Compatibilidad temporal con citas_agenda. Este flujo se mantiene
    | separado de comi_citas_agenda, tal como está definido en el esquema.
    |
    */

    private function datosAgendaLegacy(
        Request $request,
        $usuario
    ): array {
        $idEmpresaInteracom = DB::table('empresas')
            ->where('activo', true)
            ->where(function ($query) {
                $query
                    ->where('slug', 'interacom')
                    ->orWhereRaw(
                        'LOWER(nombre) = ?',
                        ['interacom']
                    );
            })
            ->orderBy('id_empresa')
            ->value('id_empresa');

        $puedeVerLegacy =
            $idEmpresaInteracom
            && (int) $usuario->id_empresa === (int) $idEmpresaInteracom;

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
            'total' => DB::table('citas_agenda')->count(),

            'pendientes' => DB::table('citas_agenda')
                ->where('estado', 'pendiente_aprobacion')
                ->count(),

            'confirmadas' => DB::table('citas_agenda')
                ->where('estado', 'confirmada')
                ->count(),

            'rechazadas' => DB::table('citas_agenda')
                ->where('estado', 'rechazada')
                ->count(),
        ];

        $consulta = DB::table('citas_agenda');

        $estado = $request->input('legacy_estado');

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
            $consulta->where('estado', $estado);
        }

        $fecha = $request->input('legacy_fecha');

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
            ->orderByDesc('fecha_hora_cita')
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


    private function autorizarAgendaLegacy(
        $usuario
    ): void {
        $idEmpresaInteracom = DB::table('empresas')
            ->where('activo', true)
            ->where(function ($query) {
                $query
                    ->where('slug', 'interacom')
                    ->orWhereRaw(
                        'LOWER(nombre) = ?',
                        ['interacom']
                    );
            })
            ->orderBy('id_empresa')
            ->value('id_empresa');

        abort_unless(
            $idEmpresaInteracom
            && (int) $usuario->id_empresa === (int) $idEmpresaInteracom,
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

        abort_unless(
            $cita,
            404
        );

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
            $datos['estado'] === 'confirmada'
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

        abort_unless(
            $cita,
            404
        );

        if (
            $cita->estado
            !== 'pendiente_aprobacion'
        ) {
            return redirect()
                ->route('comi.agenda.index')
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

        abort_unless(
            $cita,
            404
        );

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
            ->route('comi.agenda.index')
            ->with(
                'success',
                'La cita fue reprogramada correctamente.'
            );
    }


    private function agendasVisibles(

        $usuario

    ) {

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


        if (!$puedeGestionar) {

            abort(

                403,

                'No tienes permiso para gestionar esta agenda.'

            );

        }

    }

}
