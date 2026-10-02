<?php

namespace App\Services;

use RuntimeException;

class AsteriskAmiService
{
    private $socket = null;

    public function originate(
        string $extensionOperario,
        string $telefonoCliente,
        string $actionId,
        array $variables = []
    ): array {
        $this->connect();

        try {
            $this->login();

            $channel = sprintf(
                '%s/%s',
                config('asterisk.agent_tech', 'PJSIP'),
                $this->cleanValue($extensionOperario)
            );

            $headers = [
                'Action'   => 'Originate',
                'ActionID' => $this->cleanValue($actionId),
                'Channel'  => $channel,
                'Context'  => config('asterisk.outbound_context', 'from-crm'),
                'Exten'    => $this->cleanValue($telefonoCliente),
                'Priority' => '1',
                'CallerID' => sprintf(
                    '"CRM" <%s>',
                    $this->cleanValue($extensionOperario)
                ),
                'Timeout'  => (string) config('asterisk.originate_timeout', 30000),
                'Async'    => 'true',
            ];

            foreach ($variables as $key => $value) {
                $headers[] = [
                    'Variable' => $this->cleanValue((string) $key)
                        . '='
                        . $this->cleanValue((string) $value),
                ];
            }

            $response = $this->sendAction($headers);

            if (strtolower($response['Response'] ?? '') !== 'success') {
                throw new RuntimeException(
                    $response['Message']
                    ?? 'Asterisk rechazó la solicitud de llamada.'
                );
            }

            return $response;
        } finally {
            $this->logoutQuietly();
            $this->disconnect();
        }
    }

    private function connect(): void
    {
        $host = (string) config('asterisk.ami.host');
        $port = (int) config('asterisk.ami.port', 5038);
        $timeout = (float) config('asterisk.ami.connect_timeout', 5);

        if ($host === '') {
            throw new RuntimeException(
                'ASTERISK_AMI_HOST no está configurado.'
            );
        }

        $errno = 0;
        $errstr = '';

        $socket = @fsockopen(
            $host,
            $port,
            $errno,
            $errstr,
            $timeout
        );

        if (!$socket) {
            throw new RuntimeException(
                "No fue posible conectar con Asterisk AMI en {$host}:{$port}. "
                . "Error {$errno}: {$errstr}"
            );
        }

        stream_set_timeout(
            $socket,
            (int) config('asterisk.ami.read_timeout', 5)
        );

        $this->socket = $socket;

        // Banner inicial de AMI.
        fgets($this->socket);
    }

    private function login(): void
    {
        $username = (string) config('asterisk.ami.username');
        $password = (string) config('asterisk.ami.password');

        if ($username === '' || $password === '') {
            throw new RuntimeException(
                'Faltan ASTERISK_AMI_USERNAME o ASTERISK_AMI_PASSWORD.'
            );
        }

        $response = $this->sendAction([
            'Action'   => 'Login',
            'Username' => $username,
            'Secret'   => $password,
            'Events'   => 'off',
        ]);

        if (strtolower($response['Response'] ?? '') !== 'success') {
            throw new RuntimeException(
                $response['Message']
                ?? 'No fue posible iniciar sesión en Asterisk AMI.'
            );
        }
    }

    private function logoutQuietly(): void
    {
        if (!$this->socket) {
            return;
        }

        try {
            $this->sendAction([
                'Action' => 'Logoff',
            ]);
        } catch (\Throwable $e) {
            // La llamada ya fue enviada; no fallamos por el cierre de AMI.
        }
    }

    private function disconnect(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }

        $this->socket = null;
    }

    /**
     * Envía una acción AMI.
     *
     * Acepta:
     * [
     *   'Action' => 'Login',
     *   ...
     * ]
     *
     * y también líneas repetidas:
     * [
     *   'Action' => 'Originate',
     *   ['Variable' => 'A=1'],
     *   ['Variable' => 'B=2'],
     * ]
     */
    private function sendAction(array $headers): array
    {
        if (!$this->socket) {
            throw new RuntimeException('No existe conexión activa con AMI.');
        }

        $payload = '';

        foreach ($headers as $key => $value) {
            if (is_int($key) && is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    $payload .= $nestedKey
                        . ': '
                        . $this->cleanValue((string) $nestedValue)
                        . "\r\n";
                }

                continue;
            }

            $payload .= $key
                . ': '
                . $this->cleanValue((string) $value)
                . "\r\n";
        }

        $payload .= "\r\n";

        $written = fwrite($this->socket, $payload);

        if ($written === false) {
            throw new RuntimeException(
                'No fue posible enviar la acción a Asterisk AMI.'
            );
        }

        return $this->readResponse();
    }

    private function readResponse(): array
    {
        $response = [];

        while (!feof($this->socket)) {
            $line = fgets($this->socket);

            if ($line === false) {
                break;
            }

            $line = rtrim($line, "\r\n");

            if ($line === '') {
                if (!empty($response)) {
                    break;
                }

                continue;
            }

            if (!str_contains($line, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $line, 2);
            $response[trim($key)] = trim($value);
        }

        if (empty($response)) {
            throw new RuntimeException(
                'Asterisk AMI no devolvió una respuesta válida.'
            );
        }

        return $response;
    }

    private function cleanValue(string $value): string
    {
        return trim(str_replace(["\r", "\n"], '', $value));
    }
}
