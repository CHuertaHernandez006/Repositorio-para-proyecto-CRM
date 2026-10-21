<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class CitaComiConfirmada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $cita) {}

    public function build(): self
    {
        return $this->subject('Confirmación de tu cita · COMICenter / InterAcom')
            ->view('emails.cita-comi-confirmada');
    }
}
