<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrochureRequest;
use App\Models\Brochure;
use App\Services\FileStore;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class BrochureController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/Brochures/Index', [
            'brochures' => Brochure::query()->withCount('images')->orderBy('order')->get()
                ->map(fn (Brochure $brochure): array => $this->row($brochure))
                ->all(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/Brochures/Form');
    }

    public function store(BrochureRequest $request, FileStore $files): RedirectResponse
    {
        Brochure::create([
            ...$this->payload($request),
            'preview_image' => $request->hasFile('preview_image')
                ? $files->putPublic($request->file('preview_image'), 'brochures')
                : null,
            'file' => $request->hasFile('file')
                ? $files->putPublic($request->file('file'), 'brochures/pdf')
                : null,
        ]);

        Toast::success('Brosur tersimpan.');

        return to_route('admin.brochures.index');
    }

    public function edit(Brochure $brochure): Response
    {
        $brochure->loadCount('images');

        return inertia('admin/Brochures/Form', [
            'id' => (string) $brochure->id,
            'brochure' => [
                ...$this->row($brochure),
                'preview_image' => $brochure->previewUrl(),
                'file_url' => $brochure->fileUrl(),
            ],
        ]);
    }

    public function update(BrochureRequest $request, Brochure $brochure, FileStore $files): RedirectResponse
    {
        $brochure->update([
            ...$this->payload($request),
            ...($request->hasFile('preview_image')
                ? ['preview_image' => $files->putPublic($request->file('preview_image'), 'brochures', $brochure->preview_image)]
                : []),
            ...($request->hasFile('file')
                ? ['file' => $files->putPublic($request->file('file'), 'brochures/pdf', $brochure->file)]
                : []),
        ]);

        Toast::success('Perubahan tersimpan.');

        return to_route('admin.brochures.index');
    }

    public function destroy(Brochure $brochure, FileStore $files): RedirectResponse
    {
        $files->deletePublic($brochure->preview_image);
        $files->deletePublic($brochure->file);
        $brochure->delete();

        Toast::success('Brosur dihapus.');

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(BrochureRequest $request): array
    {
        return [
            'title' => $request->string('title')->value(),
            'subtitle' => $request->string('subtitle')->value(),
            'order' => $request->integer('order'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Brochure $brochure): array
    {
        return [
            'id' => $brochure->id,
            'title' => $brochure->title,
            'subtitle' => $brochure->subtitle,
            'pages' => (int) ($brochure->images_count ?? 0),
            'has_file' => $brochure->file !== null,
            'order' => $brochure->order,
            'is_active' => $brochure->is_active,
        ];
    }
}
