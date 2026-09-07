<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\PpdbRegistration;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PpdbReceivedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly PpdbRegistration $registration) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran diterima — '.$this->registration->registration_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.ppdb-received',
            with: ['school' => Setting::get('school_name', 'Sekolah')],
        );
    }
}
