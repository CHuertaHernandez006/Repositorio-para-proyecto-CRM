<?php

namespace App\Support;

use App\Models\ObjetivoOperario;
use App\Models\User;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;

/** Regla compartida por el administrador y el inicio del operario. */
class AvanceObjetivos
{
    public const ZONA = 'America/Mexico_City';

    public static function consultar(User $operario, bool $incluirHistorial = true): array
    {
        abort_unless((int) $operario->id_rol === 3 && $operario->id_empresa, 403);
        $ahora = new DateTimeImmutable('now', new DateTimeZone(self::ZONA));
        $hoy = $ahora->format('Y-m-d');
        $base = ObjetivoOperario::query()
            ->where('id_usuario', $operario->getKey())
            ->whereHas('operario', fn ($q) => $q->where('id_empresa', $operario->id_empresa));

        $calculados = [];
        $evaluar = function ($objetivo) use ($operario, $ahora, &$calculados) {
            return $calculados[$objetivo->getKey()] ??= self::calcular($operario, $objetivo, $ahora);
        };

        // Estados calculados al consultar: no dependen de que el administrador abra el panel.
        foreach ((clone $base)->whereIn('estado', ['pendiente', 'en_progreso'])->get() as $objetivo) {
            $evaluar($objetivo);
        }
        $objetivo = (clone $base)
            ->whereIn('estado', ['pendiente', 'en_progreso', 'cumplido'])
            ->whereDate('fecha_fin', '>=', $hoy)
            ->orderByDesc('id_objetivo')->first();
        $actual = $objetivo ? $evaluar($objetivo) : null;
        $historial = $incluirHistorial
            ? (clone $base)->orderByDesc('id_objetivo')->limit(5)->get()->map($evaluar)
            : collect();
        return ['actual' => $actual, 'historial' => $historial, 'zona' => self::ZONA];
    }

    public static function calcular(User $operario, ObjetivoOperario $objetivo, ?DateTimeImmutable $momento = null): array
    {
        abort_unless((int) $operario->id_rol === 3 && $operario->id_empresa
            && (int) $objetivo->id_usuario === (int) $operario->getKey(), 403);
        $zona = new DateTimeZone(self::ZONA);
        $ahora = ($momento ?? new DateTimeImmutable('now', $zona))->setTimezone($zona);
        $inicio = new DateTimeImmutable($objetivo->fecha_inicio->format('Y-m-d').' 00:00:00', $zona);
        $fin = new DateTimeImmutable($objetivo->fecha_fin->format('Y-m-d').' 00:00:00', $zona);
        $limite = $fin->modify('+1 day');
        $utc = new DateTimeZone('UTC');
        $base = DB::table('llamadas')
            ->where('id_empresa', $operario->id_empresa)
            ->where('id_usuario', $operario->getKey())
            ->where('tipo_llamada', 'saliente')
            ->where('fecha_inicio', '>=', $inicio->setTimezone($utc)->format('Y-m-d H:i:sP'))
            ->where('fecha_inicio', '<', $limite->setTimezone($utc)->format('Y-m-d H:i:sP'))
            ->where('fecha_inicio', '<=', $ahora->setTimezone($utc)->format('Y-m-d H:i:sP'));
        $conteo = (clone $base)->selectRaw(
            'COUNT(*) AS intentadas, COUNT(CASE WHEN fecha_fin IS NOT NULL AND fecha_fin <= ? THEN 1 END) AS realizadas, COUNT(CASE WHEN id_resultado IS NOT NULL THEN 1 END) AS clasificadas',
            [$ahora->setTimezone($utc)->format('Y-m-d H:i:sP')]
        )->first();
        $realizadas = (int) $conteo->realizadas;
        $meta = max(1, (int) $objetivo->objetivo_llamadas);
        $dias = (int) $ahora->setTime(0, 0)->diff($fin)->format('%r%a');
        if ($objetivo->estado === 'vencido') {
            // También se usa al sustituir una meta: no reabrirla ni completarla después.
            $estado = 'vencido';
        } elseif ($ahora < $inicio) {
            $estado = 'pendiente';
        } elseif ($realizadas >= $meta) {
            $estado = 'cumplido';
        } elseif ($ahora >= $limite) {
            $estado = 'vencido';
        } else {
            $estado = 'en_progreso';
        }
        if (in_array($objetivo->estado, ['pendiente', 'en_progreso', 'cumplido'], true) && $objetivo->estado !== $estado) {
            // Comparar el estado evita sobreescribir una sustitución concurrente del administrador.
            $cambiados = ObjetivoOperario::query()->where('id_objetivo', $objetivo->getKey())
                ->where('id_usuario', $operario->getKey())->where('estado', $objetivo->estado)
                ->update(['estado' => $estado]);
            if ($cambiados) {
                $objetivo->estado = $estado;
            }
        }
        $situacion = $estado === 'en_progreso' && $dias <= 2 ? 'proximo_vencer' : $estado;
        $etiquetas = ['pendiente' => 'Próximo a iniciar', 'en_progreso' => 'En progreso',
            'proximo_vencer' => 'Próximo a vencer', 'cumplido' => 'Cumplido', 'vencido' => 'Vencido / cerrado'];
        return [
            'objetivo' => $objetivo, 'meta' => $meta, 'realizadas' => $realizadas,
            'intentadas' => (int) $conteo->intentadas, 'clasificadas' => (int) $conteo->clasificadas,
            'restantes' => max(0, $meta - $realizadas),
            'porcentaje' => (int) round($realizadas / $meta * 100),
            'barra' => min(100, max(0, (int) round($realizadas / $meta * 100))),
            'dias_restantes' => $dias, 'estado' => $estado,
            'situacion' => $situacion, 'etiqueta' => $etiquetas[$situacion],
        ];
    }
}
