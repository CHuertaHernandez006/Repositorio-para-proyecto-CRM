@php
    $usuarioActual = auth()->user();

    $puedeLlamar =
        $usuarioActual
        && (int) $usuarioActual->id_rol === 3
        && (int) $usuarioActual->id_empresa === (int) $cliente->id_empresa
        && !empty($cliente->telefono_principal ?? $cliente->telefono_secundario);
@endphp

@if ($puedeLlamar)
    <form
        action="{{ route('llamadas.salientes.store', $cliente->id_cliente) }}"
        method="POST"
        class="crm-call-form"
        onsubmit="return confirmarLlamadaCRM(this);"
    >
        @csrf

        <button type="submit" class="crm-call-button">
            <span class="crm-call-icon" aria-hidden="true">☎</span>

            <span>
                <strong>Llamar</strong>
                <small>
                    {{ $cliente->telefono_principal ?? $cliente->telefono_secundario }}
                </small>
            </span>
        </button>
    </form>

    <style>
        .crm-call-form {
            margin: 0;
        }

        .crm-call-button {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-height: 48px;
            padding: 10px 16px;
            border: 1px solid rgba(52, 211, 153, .42);
            border-radius: 12px;
            background: rgba(52, 211, 153, .11);
            color: #dffcf1;
            font: inherit;
            cursor: pointer;
            transition:
                transform .18s ease,
                border-color .18s ease,
                background .18s ease;
        }

        .crm-call-button:hover {
            transform: translateY(-1px);
            border-color: rgba(52, 211, 153, .75);
            background: rgba(52, 211, 153, .18);
        }

        .crm-call-button:disabled {
            opacity: .6;
            cursor: wait;
            transform: none;
        }

        .crm-call-icon {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: rgba(52, 211, 153, .14);
            color: #34d399;
            font-size: 20px;
        }

        .crm-call-button strong,
        .crm-call-button small {
            display: block;
            text-align: left;
        }

        .crm-call-button small {
            margin-top: 2px;
            color: #8ea9c7;
            font-size: 12px;
            font-weight: 500;
        }
    </style>

    <script>
        function confirmarLlamadaCRM(form) {
            const button = form.querySelector('button[type="submit"]');

            if (!window.confirm(
                '¿Iniciar llamada? Primero sonará tu extensión y después Asterisk marcará al cliente.'
            )) {
                return false;
            }

            if (button) {
                button.disabled = true;

                const strong = button.querySelector('strong');

                if (strong) {
                    strong.textContent = 'Conectando...';
                }
            }

            return true;
        }
    </script>
@endif
