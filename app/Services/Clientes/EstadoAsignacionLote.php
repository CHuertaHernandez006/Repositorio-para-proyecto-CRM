<?php

namespace App\Services\Clientes;

/** Funciones sin consultas para comparar exactamente lo mostrado con lo que se va a modificar. */
class EstadoAsignacionLote
{
    public static function revision($clientes): string
    {
        $estado = [];
        foreach ($clientes as $cliente) {
            $estado[] = [(int) $cliente->id_cliente, (int) $cliente->id_usuario_responsable,
                (string) $cliente->updated_at, (bool) $cliente->excepcion,
                (string) $cliente->miembro_actualizado];
        }
        return hash('sha256', json_encode($estado, JSON_THROW_ON_ERROR));
    }

    public static function elegible($cliente, string $modo): bool
    {
        return !$cliente->excepcion
            && ($modo === 'ordinarios' || $cliente->id_usuario_responsable === null);
    }
}
