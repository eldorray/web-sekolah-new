<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Honeypot check for public forms.
 *
 * A bot fills every field it finds, and submits faster than a person can
 * read the form. Both signals are rejected the same way as a failed rule.
 *
 * ponytail: hand-rolled instead of a honeypot package — two fields and a
 * timestamp. Rate limiting still belongs on the route.
 */
trait ProtectsAgainstSpam
{
    protected function assertNotSpam(Request $request): void
    {
        if (filled($request->input('website'))) {
            throw ValidationException::withMessages([
                'website' => 'Formulir ditolak.',
            ]);
        }

        $startedAt = (int) $request->input('form_started_at', 0);

        if ($startedAt > 0 && ((int) now()->timestamp - $startedAt) < 3) {
            throw ValidationException::withMessages([
                'form_started_at' => 'Formulir terkirim terlalu cepat. Coba kirim ulang.',
            ]);
        }
    }
}
