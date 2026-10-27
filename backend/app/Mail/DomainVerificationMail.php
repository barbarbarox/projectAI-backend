<?php

namespace App\Mail;

use App\Models\DomainVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DomainVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DomainVerification $dv)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[RedSim] Kode Verifikasi Kepemilikan Domain ' . $this->dv->domain,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.domain-verification',
        );
    }
}
