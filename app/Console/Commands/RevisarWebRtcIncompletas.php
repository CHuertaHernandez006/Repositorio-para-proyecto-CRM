<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RevisarWebRtcIncompletas extends Command
{
    protected $signature = 'llamadas:revisar-webrtc {--minutos=15 : Sin señal WebRTC durante estos minutos} {--aplicar : Marcar como Sin confirmar; sin poner fecha_fin}';
    protected $description = 'Identifica llamadas WebRTC abandonadas; nunca inventa su fecha de finalización ni duración.';

    public function handle(): int
    {
        $minutos = (int) $this->option('minutos');
        if ($minutos < 5 || $minutos > 10080) {
            $this->error('El límite debe estar entre 5 y 10080 minutos.');
            return self::FAILURE;
        }
        $inicio = DB::table('estados_llamada')->whereRaw('LOWER(nombre) = LOWER(?)', ['En curso'])->value('id_estado_llamada');
        $sinConfirmar = DB::table('estados_llamada')->whereRaw('LOWER(nombre) = LOWER(?)', ['Sin confirmar'])->value('id_estado_llamada');
        if (!$inicio || !$sinConfirmar) {
            $this->error('Faltan estados de llamada. Ejecuta la migración primero.');
            return self::FAILURE;
        }
        $q = DB::table('llamadas')->where('origen_registro', 'webrtc')
            ->where('id_estado_llamada', $inicio)
            ->whereNull('fecha_fin')
            ->where('ultimo_evento_web', '<', now()->subMinutes($minutos));
        $cantidad = (clone $q)->count();
        $this->info("Llamadas WebRTC sin señal reciente: {$cantidad}");
        if (!$this->option('aplicar')) {
            $this->line('Solo diagnóstico. Para marcar como Sin confirmar usa --aplicar.');
            return self::SUCCESS;
        }
        $actualizadas = $q->update([
            'id_estado_llamada' => $sinConfirmar,
            'updated_at' => now(),
        ]);
        $this->info("Marcadas sin inventar hora de colgado: {$actualizadas}");
        return self::SUCCESS;
    }
}
