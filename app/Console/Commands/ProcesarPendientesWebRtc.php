<?php

namespace App\Console\Commands;

use App\Jobs\SincronizarSipWebRtc;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcesarPendientesWebRtc extends Command
{
    protected $signature = 'webrtc:procesar-pendientes {--limite=30}';
    protected $description = 'Recupera operaciones abandonadas y encola sincronizaciones WebRTC.';

    public function handle(): int
    {
        $limite = max(1, min(100, (int) $this->option('limite')));

        // Si un worker muere después de contactar Asterisk, se reenvía el
        // MISMO request_id y el helper responde de manera idempotente.
        $recuperadas = DB::table('webrtc_operaciones')
            ->where('estado', 'procesando')
            ->where('iniciada_en', '<', now()->subMinutes(3))
            ->update([
                'estado' => 'pendiente',
                'lease_token' => null,
                'iniciada_en' => null,
                'updated_at' => now(),
            ]);

        $ids = DB::table('webrtc_operaciones')
            ->where('estado', 'pendiente')
            ->orderBy('id')->limit($limite)->pluck('id');
        foreach ($ids as $id) {
            SincronizarSipWebRtc::dispatch((int) $id);
        }
        $this->info("Recuperadas: {$recuperadas}; encoladas: {$ids->count()}.");
        return self::SUCCESS;
    }
}
