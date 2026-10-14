<?php

namespace App\Support;

use App\Models\Cliente;

/** Reglas explícitas: no altera consultas de procesos COMI ni tareas de consola. */
class AccesoClientes
{
    public static function validarOperario($usuario): void
    {
        if ((int) $usuario->id_rol === 3) {
            abort_unless($usuario->activo && !$usuario->deleted_at
                && $usuario->estaAprobado()
                && in_array((int) $usuario->id_tipo_operario, [1, 2], true),
                403, 'Tu cuenta no está habilitada para operar.');
        }
    }

    public static function aplicar($consulta, $usuario)
    {
        self::validarOperario($usuario);
        $consulta->where('clientes.id_empresa', $usuario->id_empresa);
        if ((int) $usuario->id_rol === 3 && (int) $usuario->id_tipo_operario === 2) {
            $consulta->where('clientes.id_usuario_responsable', $usuario->id_usuario);
        }
        return $consulta;
    }

    public static function consulta($usuario)
    {
        return self::aplicar(Cliente::query(), $usuario);
    }

    public static function filtrarRelacion($consulta, $usuario, string $columna)
    {
        if ((int) $usuario->id_rol === 3) {
            $consulta->whereIn($columna, self::consulta($usuario)->select('clientes.id_cliente'));
        }
        return $consulta;
    }

    public static function autorizarCliente($usuario, $id): void
    {
        abort_unless(self::consulta($usuario)->where('clientes.id_cliente', $id)->exists(),
            403, 'No tienes permiso para gestionar este cliente.');
    }

    public static function autorizarAlta($usuario): void
    {
        self::validarOperario($usuario);
        abort_unless((int) $usuario->id_rol === 2 ||
            ((int) $usuario->id_rol === 3 && (int) $usuario->id_tipo_operario === 1),
            403, 'Tu perfil no tiene permisos para registrar clientes nuevos.');
    }
}
