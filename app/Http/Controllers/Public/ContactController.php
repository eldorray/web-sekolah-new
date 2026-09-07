<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Concerns\ProtectsAgainstSpam;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Http\Requests\StoreVisitScheduleRequest;
use App\Mail\ContactAdminAlertMail;
use App\Models\ContactMessage;
use App\Models\Setting;
use App\Models\VisitSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    use ProtectsAgainstSpam;

    public function index(): Response
    {
        return inertia('public/Contact', [
            'map_embed' => Setting::get('map_embed'),
        ]);
    }

    public function storeMessage(StoreContactMessageRequest $request): RedirectResponse
    {
        $this->assertNotSpam($request);

        $message = ContactMessage::create($request->validated());

        $adminEmail = Setting::get('email');

        if (filled($adminEmail)) {
            Mail::to($adminEmail)->send(new ContactAdminAlertMail($message));
        }

        Inertia::flash('contact_sent', true);

        return back();
    }

    public function storeVisit(StoreVisitScheduleRequest $request): RedirectResponse
    {
        $this->assertNotSpam($request);

        VisitSchedule::create($request->validated());

        Inertia::flash('visit_sent', true);

        return back();
    }
}
