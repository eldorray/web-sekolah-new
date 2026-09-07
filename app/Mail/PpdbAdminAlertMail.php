<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\PpdbRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PpdbAdminAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly PpdbRegistration $registration) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftar baru: '.$this->registration->full_name,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.ppdb-admin-alert');
    }
}
