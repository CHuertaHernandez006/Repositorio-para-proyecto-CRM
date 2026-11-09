<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\AsteriskWebRtcProvisioningService;
use App\Jobs\SincronizarSipWebRtc;

class ExtensionAsteriskController extends Controller
{
    /**
     * Super Admin (rol 1): todas las empresas.
     * Admin Cliente (rol 2): exclusivamente su propia empresa.
     */
    private function empresaAutorizada(Request $request): int
    {
        $actor = $request->user();
        abort_unless($actor && in_array((int) $actor->id_rol, [1, 2], true), 403);

        $empresa = (int) $request->input('id_empresa', $actor->id_empresa);

        if ((int) $actor->id_rol === 2) {
            abort_unless($empresa === (int) $actor->id_empresa, 403);
        }

        abort_unless(DB::table('empresas')->where('id_empresa', $empresa)->exists(), 404);

        return $empresa;
    }

    public function index(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor && in_array((int) $actor->id_rol, [1, 2], true), 403);

        $empresas = (int) $actor->id_rol === 1
            ? DB::table('empresas')->orderBy('nombre')->get(['id_empresa', 'nombre'])
            : DB::table('empresas')->where('id_empresa', $actor->id_empresa)
                ->get(['id_empresa', 'nombre']);

        $empresaDefault = $empresas->firstWhere('id_empresa', 55) ?? $empresas->first();
        $idEmpresa = (int) $request->input(
            'id_empresa',
            (int) $actor->id_rol === 1 ? ($empresaDefault->id_empresa ?? 0) : $actor->id_empresa
        );

        // El selector en el GET también respeta la autorización de rol.
        abort_unless($empresas->contains('id_empresa', $idEmpresa), 403);

        $operarios = DB::table('usuarios')
            ->where('id_empresa', $idEmpresa)
            ->where('id_rol', 3)
            ->whereNull('deleted_at')
            ->orderBy('nombre')->orderBy('apellido_paterno')
            ->get(['id_usuario', 'nombre', 'apellido_paterno', 'extension_asterisk', 'activo']);

        $inventario = DB::table('extensiones_asterisk as e')
            ->leftJoin('usuarios as u', 'u.extension_asterisk', '=', 'e.extension')
            ->where('e.id_empresa', $idEmpresa)
            ->orderBy('e.extension')
            ->get([
                'e.extension', 'e.activa', 'u.id_usuario as asignado_a',
                'u.nombre as asignado_nombre', 'u.apellido_paterno as asignado_apellido',
            ]);

        $credencialesWebRtc = DB::table('webrtc_credenciales')
            ->whereIn('id_usuario', $operarios->pluck('id_usuario'))
            ->get()->keyBy('id_usuario');
        $ultimasOperacionesWebRtc = DB::table('webrtc_operaciones')
            ->where('id_empresa', $idEmpresa)
            ->orderByDesc('id')->get()->unique('extension_asterisk')
            ->keyBy('extension_asterisk');
        $extensionesGestionadas = config('webrtc_provisioning.managed_extensions', []);
        $provisionadorListo = app(AsteriskWebRtcProvisioningService::class)->estaListo();

        return view('operarios.extensiones-sip', compact(
            'empresas', 'idEmpresa', 'operarios', 'inventario',
            'credencialesWebRtc', 'ultimasOperacionesWebRtc',
            'extensionesGestionadas', 'provisionadorListo'
        ));
    }

    public function registrar(Request $request)
    {
        $idEmpresa = $this->empresaAutorizada($request);
        $datos = $request->validate(['extensiones' => ['required', 'string', 'max:5000']]);

        // Admite números separados por saltos de línea, espacios, comas o ;.
        $codigos = array_values(array_unique(array_filter(
            preg_split('/[\s,;]+/u', trim($datos['extensiones']))
        )));

        if (count($codigos) < 1 || count($codigos) > 100) {
            throw ValidationException::withMessages([
                'extensiones' => 'Registra entre 1 y 100 extensiones por operación.',
            ]);
        }

        foreach ($codigos as $codigo) {
            if (!preg_match('/^[0-9]{3,6}$/D', $codigo)) {
                throw ValidationException::withMessages([
                    'extensiones' => "La extensión {$codigo} no es válida (3 a 6 dígitos).",
                ]);
            }
        }

        $repetidos = DB::table('extensiones_asterisk')
            ->whereIn('extension', $codigos)->pluck('extension')->all();
        if ($repetidos) {
            throw ValidationException::withMessages([
                'extensiones' => 'Ya están registradas: ' . implode(', ', $repetidos) . '.',
            ]);
        }

        $fecha = now();
        $filas = array_map(fn ($codigo) => [
            'id_empresa' => $idEmpresa,
            'extension' => $codigo,
            'activa' => true,
            'created_at' => $fecha,
            'updated_at' => $fecha,
        ], $codigos);

        try {
            DB::table('extensiones_asterisk')->insert($filas);
        } catch (QueryException $e) {
            // Puede existir una inserción concurrente, protegida por UNIQUE.
            throw ValidationException::withMessages([
                'extensiones' => 'Una o más extensiones acaban de registrarse. Actualiza e inténtalo de nuevo.',
            ]);
        }

        return redirect()->route('extensiones-sip.index', ['id_empresa' => $idEmpresa])
            ->with('exito', count($codigos) . ' extensiones registradas en el catálogo CRM.');
    }

    public function asignar(Request $request, AsteriskWebRtcProvisioningService $provisionador)
    {
        $idEmpresa = $this->empresaAutorizada($request);
        $datos = $request->validate([
            'id_usuario' => ['required', 'integer'],
            'extension' => ['required', 'regex:/^[0-9]{3,6}$/D'],
        ]);
        $operaciones = [];

        try {
            $operaciones = DB::transaction(function () use ($idEmpresa, $datos, $provisionador) {
                $nueva = (string) $datos['extension'];
                $extension = DB::table('extensiones_asterisk')
                    ->where('id_empresa', $idEmpresa)
                    ->where('extension', $nueva)
                    ->lockForUpdate()->first();

                if (!$extension || !$extension->activa) {
                    throw ValidationException::withMessages([
                        'extension' => 'La extension no esta disponible en esta empresa.',
                    ]);
                }

                $operario = DB::table('usuarios')
                    ->where('id_empresa', $idEmpresa)
                    ->where('id_usuario', $datos['id_usuario'])
                    ->where('id_rol', 3)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()->first();
                if (!$operario) {
                    throw ValidationException::withMessages([
                        'id_usuario' => 'El operario no pertenece a esta empresa.',
                    ]);
                }

                $ocupada = DB::table('usuarios')
                    ->where('extension_asterisk', $nueva)
                    ->where('id_usuario', '<>', $operario->id_usuario)
                    ->exists();
                if ($ocupada) {
                    throw ValidationException::withMessages([
                        'extension' => 'La extension ya esta asignada a otro usuario.',
                    ]);
                }
                $provisionador->validarRevocacionPrevia($nueva);

                $anterior = trim((string) ($operario->extension_asterisk ?? ''));
                $ids = [];
                if ($anterior !== '' && $anterior !== $nueva) {
                    $revocacion = $provisionador->prepararRevocacion(
                        (int) $operario->id_usuario, $idEmpresa, $anterior
                    );
                    if ($revocacion !== null) $ids[] = $revocacion;
                }

                DB::table('usuarios')->where('id_usuario', $operario->id_usuario)
                    ->update(['extension_asterisk' => $nueva, 'updated_at' => now()]);

                if ($provisionador->esGestionada($nueva)) {
                    $actual = DB::table('webrtc_credenciales')
                        ->where('id_usuario', $operario->id_usuario)
                        ->where('extension_asterisk', $nueva)->first();
                    if (!$actual) {
                        $ocupadaEnWeb = DB::table('webrtc_credenciales')
                            ->where('extension_asterisk', $nueva)->exists();
                        if ($ocupadaEnWeb) {
                            throw ValidationException::withMessages([
                                'extension' => 'Esta extension conserva otra credencial. Solicita revocacion previa.',
                            ]);
                        }
                        $ids[] = $provisionador->prepararProvision(
                            (int) $operario->id_usuario, $idEmpresa, $nueva
                        );
                    }
                }
                return $ids;
            }, 3);
        } catch (QueryException $e) {
            throw ValidationException::withMessages([
                'extension' => 'No se pudo completar la asignacion. Actualiza e intentalo de nuevo.',
            ]);
        }

        $this->despacharSiConfigurado($operaciones, $provisionador);
        $mensaje = $operaciones
            ? 'Extension asignada. Sincronizacion WebRTC pendiente de confirmacion por Asterisk.'
            : 'Extension asignada. No se modificaron credenciales SIP existentes.';

        return redirect()->route('extensiones-sip.index', ['id_empresa' => $idEmpresa])
            ->with('exito', $mensaje);
    }

    public function liberar(Request $request, AsteriskWebRtcProvisioningService $provisionador)
    {
        $idEmpresa = $this->empresaAutorizada($request);
        $datos = $request->validate(['id_usuario' => ['required', 'integer']]);

        $operaciones = DB::transaction(function () use ($idEmpresa, $datos, $provisionador) {
            $operario = DB::table('usuarios')
                ->where('id_empresa', $idEmpresa)
                ->where('id_usuario', $datos['id_usuario'])
                ->where('id_rol', 3)
                ->whereNull('deleted_at')
                ->lockForUpdate()->first();

            if (!$operario) {
                throw ValidationException::withMessages([
                    'id_usuario' => 'El operario no pertenece a esta empresa.',
                ]);
            }

            $anterior = trim((string) ($operario->extension_asterisk ?? ''));
            $operaciones = [];
            if ($anterior !== '') {
                $idOperacion = $provisionador->prepararRevocacion(
                    (int) $operario->id_usuario, $idEmpresa, $anterior
                );
                if ($idOperacion !== null) $operaciones[] = $idOperacion;
            }

            DB::table('usuarios')->where('id_usuario', $operario->id_usuario)
                ->update(['extension_asterisk' => null, 'updated_at' => now()]);
            return $operaciones;
        }, 3);

        $this->despacharSiConfigurado($operaciones, $provisionador);
        return redirect()->route('extensiones-sip.index', ['id_empresa' => $idEmpresa])
            ->with('exito', $operaciones
                ? 'Extension liberada en CRM; revocacion SIP pendiente de confirmacion.'
                : 'Extension liberada. No se altero el Asterisk tradicional.');
    }

    public function renovar(Request $request, AsteriskWebRtcProvisioningService $provisionador)
    {
        $idEmpresa = $this->empresaAutorizada($request);
        $datos = $request->validate(['id_usuario' => ['required', 'integer']]);

        $idOperacion = DB::transaction(function () use ($idEmpresa, $datos, $provisionador) {
            $operario = DB::table('usuarios')->where('id_empresa', $idEmpresa)
                ->where('id_usuario', $datos['id_usuario'])->where('id_rol', 3)
                ->whereNull('deleted_at')->lockForUpdate()->first();
            if (!$operario) {
                throw ValidationException::withMessages(['id_usuario' => 'Operario no autorizado.']);
            }
            $extension = trim((string) ($operario->extension_asterisk ?? ''));
            if (!$provisionador->esGestionada($extension)) {
                throw ValidationException::withMessages([
                    'id_usuario' => 'Esta extension no esta habilitada para credenciales WebRTC automaticas.',
                ]);
            }
            $pendientes = DB::table('webrtc_operaciones')
                ->where('extension_asterisk', $extension)
                ->whereNotIn('estado', ['completada', 'cancelada'])
                ->exists();
            if ($pendientes) {
                throw ValidationException::withMessages([
                    'id_usuario' => 'Hay una operacion WebRTC pendiente o con error. Resuelvela antes de renovar.',
                ]);
            }
            return $provisionador->prepararProvision((int) $operario->id_usuario, $idEmpresa, $extension);
        }, 3);
        $this->despacharSiConfigurado([$idOperacion], $provisionador);
        return redirect()->route('extensiones-sip.index', ['id_empresa' => $idEmpresa])
            ->with('exito', 'Renovacion solicitada; Asterisk debe confirmar la nueva clave antes de usarla.');
    }

    public function reintentar(Request $request, AsteriskWebRtcProvisioningService $provisionador)
    {
        $idEmpresa = $this->empresaAutorizada($request);
        $datos = $request->validate(['id_operacion' => ['required', 'integer']]);
        $id = (int) $datos['id_operacion'];

        DB::transaction(function () use ($idEmpresa, $id) {
            $op = DB::table('webrtc_operaciones')->where('id', $id)
                ->where('id_empresa', $idEmpresa)->lockForUpdate()->first();
            if (!$op || !in_array($op->estado, ['pendiente', 'error'], true)) {
                throw ValidationException::withMessages(['id_operacion' => 'Operacion inexistente o ya procesada.']);
            }
            DB::table('webrtc_operaciones')->where('id', $id)->update([
                'estado' => 'pendiente', 'ultimo_error' => null, 'updated_at' => now(),
            ]);
            if ($op->tipo === 'provisionar') {
                DB::table('webrtc_credenciales')
                    ->where('solicitud_uuid', $op->solicitud_uuid)
                    ->update(['estado' => 'pendiente', 'ultimo_error' => null, 'updated_at' => now()]);
            }
        }, 3);
        $this->despacharSiConfigurado([$id], $provisionador);
        return redirect()->route('extensiones-sip.index', ['id_empresa' => $idEmpresa])
            ->with('exito', 'Se solicito reintentar la sincronizacion WebRTC.');
    }

    private function despacharSiConfigurado(array $ids, AsteriskWebRtcProvisioningService $provisionador): void
    {
        if (!$provisionador->estaListo()) return;
        foreach ($ids as $id) {
            SincronizarSipWebRtc::dispatch((int) $id);
        }
    }
}
