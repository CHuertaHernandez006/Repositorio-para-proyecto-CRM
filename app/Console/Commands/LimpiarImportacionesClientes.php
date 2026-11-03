<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimpiarImportacionesClientes extends Command
{
    protected $signature = 'clientes:limpiar-importaciones';
    protected $description = 'Elimina vistas previas y resultados de importación que ya expiraron; no elimina clientes.';

    public function handle(): int
    {
        $cantidad = DB::table('importaciones_clientes')->where('vence_at', '<=', now())->delete();
        $this->info('Importaciones expiradas eliminadas: '.$cantidad);
        return self::SUCCESS;
    }
}
