<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AdiwiyataAssessment;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Response;

class AdiwiyataController extends Controller
{
    public function index(Request $request): Response
    {
        $assessments = AdiwiyataAssessment::query()->get()
            ->keyBy('folder_key')
            ->map(fn (AdiwiyataAssessment $row): array => [
                'status' => $row->status,
                'note' => $row->note,
            ])
            ->all();

        return inertia('public/Adiwiyata', [
            'tree' => $this->tree(),
            'assessments' => $assessments,
            // Progress is public; only an admin may change a folder status.
            'canEdit' => $request->user()?->isAdmin() === true,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'folder_key' => ['required', 'string', 'max:255', Rule::in($this->folderKeys())],
            'status' => ['required', Rule::in(AdiwiyataAssessment::STATUSES)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        AdiwiyataAssessment::updateOrCreate(
            ['folder_key' => $validated['folder_key']],
            ['status' => $validated['status'], 'note' => $validated['note'] ?? null],
        );

        Toast::success('Penilaian tersimpan.');

        return back();
    }

    public function reset(): RedirectResponse
    {
        AdiwiyataAssessment::query()->delete();

        Toast::success('Semua penilaian direset.');

        return back();
    }

    /**
     * @return list<array{key: string, title: string, children: list<array{key: string, title: string}>}>
     */
    private function tree(): array
    {
        $path = resource_path('data/adiwiyata-tree.json');

        if (! is_file($path)) {
            return [];
        }

        return json_decode((string) file_get_contents($path), true) ?? [];
    }

    /**
     * @return list<string>
     */
    private function folderKeys(): array
    {
        $keys = [];

        foreach ($this->tree() as $group) {
            foreach ($group['children'] as $folder) {
                $keys[] = $folder['key'];
            }
        }

        return $keys;
    }
}
