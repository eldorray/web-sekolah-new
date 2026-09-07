<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactAdminAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly ContactMessage $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pesan baru: '.$this->message->subject);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.contact-admin-alert');
    }
}
