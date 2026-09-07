<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Concerns\ProtectsAgainstSpam;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePpdbRegistrationRequest;
use App\Mail\PpdbAdminAlertMail;
use App\Mail\PpdbReceivedMail;
use App\Models\PpdbRegistration;
use App\Models\Setting;
use App\Services\FileStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class PpdbController extends Controller
{
    use ProtectsAgainstSpam;

    public function create(): Response
    {
        return inertia('public/Ppdb', [
            'steps' => SettingController::list('ppdb_steps'),
            'intro' => Setting::get('ppdb_intro'),
        ]);
    }

    public function store(StorePpdbRegistrationRequest $request, FileStore $files): RedirectResponse
    {
        $this->assertNotSpam($request);

        $registration = PpdbRegistration::createWithNumber([
            ...$request->safe()->except(['kk_file', 'birth_certificate_file']),
            // Documents stay on the private disk; only admins can read them.
            'kk_file' => $files->putPrivate($request->file('kk_file'), 'ppdb/kk'),
            'birth_certificate_file' => $files->putPrivate(
                $request->file('birth_certificate_file'),
                'ppdb/akte',
            ),
        ]);

        Mail::to($registration->parent_email)->send(new PpdbReceivedMail($registration));

        $adminEmail = Setting::get('email');

        if (filled($adminEmail)) {
            Mail::to($adminEmail)->send(new PpdbAdminAlertMail($registration));
        }

        Inertia::flash('registration_number', $registration->registration_number);

        return back();
    }
}
