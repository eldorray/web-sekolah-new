<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/Contacts', [
            'messages' => ContactMessage::query()->latest()->get()
                ->map(fn (ContactMessage $row): array => [
                    'id' => $row->id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'phone' => $row->phone,
                    'subject' => $row->subject,
                    'message' => $row->message,
                    'created_at' => $row->created_at?->translatedFormat('j F Y'),
                    'is_read' => $row->is_read,
                ])
                ->all(),
        ]);
    }

    public function markRead(ContactMessage $contact): RedirectResponse
    {
        $contact->update(['is_read' => true]);

        Toast::success('Pesan ditandai sudah dibaca.');

        return back();
    }

    public function destroy(ContactMessage $contact): RedirectResponse
    {
        $contact->delete();

        Toast::success('Pesan dihapus.');

        return back();
    }
}
