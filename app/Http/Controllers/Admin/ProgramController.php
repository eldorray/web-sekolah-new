<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramRequest;
use App\Models\Program;
use App\Services\FileStore;
use App\Services\HtmlSanitizer;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Response;

class ProgramController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/Programs/Index', [
            'programs' => Program::query()->orderBy('order')->get()
                ->map(fn (Program $program): array => $this->row($program))
                ->all(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/Programs/Form');
    }

    public function store(ProgramRequest $request, HtmlSanitizer $sanitizer, FileStore $files): RedirectResponse
    {
        Program::create([
            ...$this->payload($request, $sanitizer),
            'slug' => $this->uniqueSlug($request->string('title')->value()),
            'image' => $request->hasFile('image')
                ? $files->putPublic($request->file('image'), 'programs')
                : null,
        ]);

        Toast::success('Program tersimpan.');

        return to_route('admin.programs.index');
    }

    public function edit(Program $program): Response
    {
        return inertia('admin/Programs/Form', [
            'slug' => $program->slug,
            'program' => [
                ...$this->row($program),
                'badge' => $program->badge,
                'short_description' => $program->short_description,
                'description' => $program->description,
                'image' => $program->imageUrl(),
            ],
        ]);
    }

    public function update(ProgramRequest $request, Program $program, HtmlSanitizer $sanitizer, FileStore $files): RedirectResponse
    {
        $program->update([
            ...$this->payload($request, $sanitizer),
            'slug' => $program->title === $request->string('title')->value()
                ? $program->slug
                : $this->uniqueSlug($request->string('title')->value(), $program->id),
            ...($request->hasFile('image')
                ? ['image' => $files->putPublic($request->file('image'), 'programs', $program->image)]
                : []),
        ]);

        Toast::success('Perubahan tersimpan.');

        return to_route('admin.programs.index');
    }

    public function destroy(Program $program, FileStore $files): RedirectResponse
    {
        $files->deletePublic($program->image);
        $program->delete();

        Toast::success('Program dihapus.');

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(ProgramRequest $request, HtmlSanitizer $sanitizer): array
    {
        return [
            'title' => $request->string('title')->value(),
            'icon' => $request->string('icon')->value(),
            'badge' => $request->string('badge')->value(),
            'short_description' => $request->string('short_description')->value(),
            'description' => $sanitizer->clean($request->string('description')->value()),
            'order' => $request->integer('order'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (Program::query()->where('slug', $slug)->whereKeyNot($ignoreId ?? 0)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Program $program): array
    {
        return [
            'slug' => $program->slug,
            'title' => $program->title,
            'icon' => $program->icon,
            'order' => $program->order,
            'is_active' => $program->is_active,
        ];
    }
}
