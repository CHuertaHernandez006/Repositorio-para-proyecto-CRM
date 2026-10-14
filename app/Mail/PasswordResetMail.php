<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable;
    use SerializesModels;


    public function __construct(
        public User $usuario,
        public string $url
    ) {
        //
    }


    public function build(): self
    {
        return $this
            ->subject(
                'Recuperación de contraseña - COMICenter'
            )
            ->view(
                'emails.password-reset'
            );
    }
}