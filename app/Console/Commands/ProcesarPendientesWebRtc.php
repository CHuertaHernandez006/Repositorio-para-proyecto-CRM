<?php

namespace App\Console\Commands;

use App\Jobs\SincronizarSipWebRtc;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcesarPendientesWebRtc extends Command
{
    protected $signature = 'webrtc:procesar-pendientes {--limite=30}';
    protected $description = 'Encola operaciones pendientes del telefono WebRTC.';

    public function handle(): int
    {
        $limite = max(1, min(100, (int) $this->option('limite')));
        $ids = DB::table('webrtc_operaciones')->where('estado', 'pendiente')
            ->orderBy('id')->limit($limite)->pluck('id');
        foreach ($ids as $id) {
            SincronizarSipWebRtc::dispatch((int) $id);
        }
        $this->info('Operaciones encoladas: ' . $ids->count());
        return self::SUCCESS;
    }
}
