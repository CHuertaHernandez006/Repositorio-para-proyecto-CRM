<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReorganizarDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $alanTech = DB::table('users')
                ->where('email', 'chavitoacosta5@gmail.com')
                ->where('id_rol', 2)
                ->value('id_empresa');

            $charlyTech = DB::table('users')
                ->where('email', 'cris@gmail.com')
                ->where('id_rol', 2)
                ->value('id_empresa');

            if (!$alanTech || !$charlyTech || $alanTech == $charlyTech) {
                throw new RuntimeException(
                    'Los administradores deben tener dos empresas distintas. No se aplicaron cambios.'
                );
            }

            $idsEmpresas = [$alanTech, $charlyTech];

            if (
                DB::table('empresas')
                    ->whereIn('id_empresa', $idsEmpresas)
                    ->count() !== 2
            ) {
                throw new RuntimeException(
                    'No se encontraron ambas empresas. No se aplicaron cambios.'
                );
            }

            // Evitar que el borrado de otras empresas elimine usuarios.
            $hayUsuariosVinculados = DB::table('users')
                ->whereNotNull('id_empresa')
                ->whereNotIn('id_empresa', $idsEmpresas)
                ->exists();

            if ($hayUsuariosVinculados) {
                throw new RuntimeException(
                    'Hay usuarios vinculados a otras empresas. '
                    . 'Es necesario reasignarlos antes de continuar.'
                );
            }

            // Guardar los usuarios para comprobar que permanezcan intactos.
            $usuariosAntes = DB::table('users')
                ->orderBy('id')
                ->get()
                ->toJson();

            // Reemplazar únicamente los clientes de prueba.
            DB::table('clientes')->delete();

            // Conservar las empresas de los dos administradores.
            DB::table('empresas')
                ->whereNotIn('id_empresa', $idsEmpresas)
                ->delete();

            DB::table('empresas')
                ->where('id_empresa', $alanTech)
                ->update([
                    'nombre' => 'AlanTech Solutions, S.A. de C.V.',
                    'estado' => true,
                    'updated_at' => now(),
                ]);

            DB::table('empresas')
                ->where('id_empresa', $charlyTech)
                ->update([
                    'nombre' => 'CharlyTech Solutions, S.A. de C.V.',
                    'estado' => true,
                    'updated_at' => now(),
                ]);

            // Este seeder solo inserta contactos en clientes.
            $this->call(ClientesCsvSeeder::class);

            $clientes = DB::table('clientes')
                ->orderBy('id_cliente')
                ->pluck('id_cliente');

            if ($clientes->count() !== 50) {
                throw new RuntimeException(
                    'Se esperaban exactamente 50 clientes. Se cancelaron los cambios.'
                );
            }

            // Primeros 25 contactos para AlanTech.
            DB::table('clientes')
                ->whereIn('id_cliente', $clientes->take(25)->all())
                ->update([
                    'id_empresa' => $alanTech,
                    'updated_at' => now(),
                ]);

            // Últimos 25 contactos para CharlyTech.
            DB::table('clientes')
                ->whereIn('id_cliente', $clientes->slice(25)->all())
                ->update([
                    'id_empresa' => $charlyTech,
                    'updated_at' => now(),
                ]);

            $usuariosDespues = DB::table('users')
                ->orderBy('id')
                ->get()
                ->toJson();

            if ($usuariosAntes !== $usuariosDespues) {
                throw new RuntimeException(
                    'Se detectó una modificación de usuarios. Se cancelaron los cambios.'
                );
            }
        });

        $this->command->info(
            'Listo: dos empresas, 25 clientes por empresa y usuarios intactos.'
        );
    }
}