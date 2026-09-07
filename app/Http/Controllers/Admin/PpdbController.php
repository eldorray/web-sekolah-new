<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpdbRegistration;
use App\Support\PpdbCsv;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PpdbController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/Ppdb/Index', [
            'registrations' => PpdbRegistration::query()->latest()->get()
                ->map(fn (PpdbRegistration $row): array => $this->row($row))
                ->all(),
        ]);
    }

    public function show(PpdbRegistration $registration): Response
    {
        return inertia('admin/Ppdb/Show', [
            'number' => $registration->registration_number,
            'registration' => [
                ...$this->row($registration),
                'nickname' => $registration->nickname,
                'birthplace' => $registration->birthplace,
                'birthdate' => $registration->birthdate?->toDateString(),
                'previous_school' => $registration->previous_school,
                'address' => $registration->address,
                'father_name' => $registration->father_name,
                'mother_name' => $registration->mother_name,
                'parent_email' => $registration->parent_email,
                'notes' => $registration->notes,
                'has_kk' => $registration->kk_file !== null,
                'has_birth_certificate' => $registration->birth_certificate_file !== null,
            ],
        ]);
    }

    public function update(Request $request, PpdbRegistration $registration): RedirectResponse
    {
        $registration->update($request->validate([
            'status' => ['required', Rule::in(PpdbRegistration::STATUSES)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]));

        Toast::success('Status pendaftar diperbarui.');

        return back();
    }

    /** Private documents are streamed through here, never served from the web root. */
    public function document(PpdbRegistration $registration, string $type): BinaryFileResponse
    {
        $path = match ($type) {
            'kk' => $registration->kk_file,
            'akte' => $registration->birth_certificate_file,
            default => abort(404),
        };

        abort_if($path === null || ! Storage::disk('local')->exists($path), 404);

        return response()->download(Storage::disk('local')->path($path));
    }

    public function export(PpdbCsv $csv): StreamedResponse
    {
        $content = $csv->build(PpdbRegistration::query()->latest()->get());

        return response()->streamDownload(
            fn () => print ($content),
            'ppdb-'.now()->format('Y-m-d').'.csv',
            ['Content-Type' => 'text/csv'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function row(PpdbRegistration $row): array
    {
        return [
            'number' => $row->registration_number,
            'full_name' => $row->full_name,
            'gender' => $row->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            'grade_target' => $row->grade_target,
            'parent_phone' => $row->parent_phone,
            'created_at' => $row->created_at?->translatedFormat('j F Y'),
            'status' => $row->status,
        ];
    }
}
