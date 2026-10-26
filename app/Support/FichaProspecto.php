<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class FichaProspecto
{
    public const CONTACTO = [
        'nombre' => 'Nombre', 'apellido_paterno' => 'Apellido paterno',
        'apellido_materno' => 'Apellido materno', 'organizacion' => 'Empresa',
        'telefono_principal' => 'Teléfono principal', 'telefono_secundario' => 'Teléfono secundario',
        'correo' => 'Correo',
    ];
    public const NECESIDADES = [
        'giro' => 'Giro', 'numero_colaboradores' => 'Colaboradores',
        'canal_principal' => 'Canal principal', 'volumen_estimado' => 'Volumen estimado',
        'presupuesto_aproximado' => 'Presupuesto indicado por el prospecto',
        'objetivo_principal' => 'Objetivo principal',
    ];

    public static function datos($p, bool $bloquear = false): array
    {
        // El controlador ya verificó el propietario comercial y el asesor del prospecto.
        $clienteQuery = DB::table('clientes')->where('id_empresa', $p->id_empresa_origen)
            ->where('id_cliente', $p->id_cliente)->whereNull('deleted_at');
        $manualQuery = DB::table('comercial_fichas_prospecto')->where('id_prospecto', $p->id);
        if ($bloquear) {
            $clienteQuery->lockForUpdate();
            $manualQuery->lockForUpdate();
        }
        $cliente = $clienteQuery->first();
        abort_unless($cliente, 404);
        $manual = $manualQuery->first();
        $original = DB::table('comi_calificaciones_lead')
            ->where('id_empresa', $p->id_empresa_origen)->where('id_cliente', $p->id_cliente)
            ->orderByDesc('id_calificacion')->first();
        $necesidades = [];
        foreach (self::NECESIDADES as $campo => $etiqueta) {
            $necesidades[$campo] = $manual ? $manual->$campo : ($original->$campo ?? null);
        }
        $editor = $manual ? DB::table('usuarios')->where('id_usuario', $manual->id_editor)
            ->select('nombre', 'apellido_paterno')->first() : null;
        return compact('cliente', 'manual', 'original', 'necesidades', 'editor');
    }

    public static function version(array $ficha): string
    {
        $contacto = [];
        foreach (self::CONTACTO as $campo => $etiqueta) {
            $contacto[$campo] = $ficha['cliente']->$campo;
        }
        return hash('sha256', json_encode([
            $contacto, $ficha['necesidades'], $ficha['manual']->updated_at ?? null,
            $ficha['cliente']->updated_at, $ficha['original']->id_calificacion ?? null,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}
