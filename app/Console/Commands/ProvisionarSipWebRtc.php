<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ProvisionarSipWebRtc extends Command
{
    protected $signature = 'webrtc:provisionar
        {id_usuario : ID interno del operario}
        {--extension= : Extension SIP asignada por Redes}';

    protected $description = 'Provisionar SIP WebRTC por operario, con clave cifrada (sin poner el secreto en el comando).';

    public function handle(): int
    {
        $idUsuario = filter_var($this->argument('id_usuario'), FILTER_VALIDATE_INT);
        if (!$idUsuario || $idUsuario < 1) {
            $this->error('ID de usuario invalido.');
            return self::FAILURE;
        }

        $usuario = DB::table('usuarios')->where('id_usuario', $idUsuario)->first();
        if (!$usuario || (int) $usuario->id_rol !== 3) {
            $this->error('El usuario no existe o no es un operario.');
            return self::FAILURE;
        }

        $extensionAsignada = trim((string) $usuario->extension_asterisk);
        $extension = trim((string) ($this->option('extension') ?? $extensionAsignada));
        if (!preg_match('/^[0-9]{3,6}$/', $extension) || $extension !== $extensionAsignada) {
            $this->error('La extension debe coincidir con usuarios.extension_asterisk.');
            return self::FAILURE;
        }

        $extensionOcupada = DB::table('webrtc_credenciales')
            ->where('extension_asterisk', $extension)
            ->where('id_usuario', '<>', $idUsuario)
            ->exists();
        if ($extensionOcupada) {
            $this->error('Esa extension ya tiene credencial asociada a otro operario.');
            return self::FAILURE;
        }

        $this->info("Provisionando extension {$extension} al operario #{$idUsuario}.");
        $this->warn('La clave debe ser la nueva credencial SIP vigente en Asterisk.');
        $secret = $this->secret('Contraseña SIP (no se muestra)');
        if (!is_string($secret) || $secret === '') {
            $this->error('No se guardo una clave vacia.');
            return self::FAILURE;
        }

        DB::table('webrtc_credenciales')->upsert(
            [[
                'id_usuario' => $idUsuario,
                'extension_asterisk' => $extension,
                'secret_cifrado' => Crypt::encryptString($secret),
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['id_usuario'],
            ['extension_asterisk', 'secret_cifrado', 'updated_at']
        );
        unset($secret);

        $this->info('Credencial cifrada para el operario. No se imprimio ni guardo en Git.');
        return self::SUCCESS;
    }
}
