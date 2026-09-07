<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use App\Models\User;
use App\Services\FileStore;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Response;

class TeacherController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/Teachers/Index', [
            'teachers' => User::query()->orderBy('name')->get()
                ->map(fn (User $user): array => $this->row($user))
                ->all(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/Teachers/Form');
    }

    public function store(TeacherRequest $request, FileStore $files): RedirectResponse
    {
        User::create([
            ...$this->payload($request),
            'password' => Hash::make((string) $request->string('password')->value()),
            'photo' => $request->hasFile('photo')
                ? $files->putPublic($request->file('photo'), 'teachers')
                : null,
        ]);

        Toast::success('Data guru tersimpan.');

        return to_route('admin.teachers.index');
    }

    public function edit(User $teacher): Response
    {
        return inertia('admin/Teachers/Form', [
            'id' => (string) $teacher->id,
            'teacher' => [
                ...$this->row($teacher),
                'phone' => $teacher->phone,
                'bio' => $teacher->bio,
                'instagram' => $teacher->instagram,
                'facebook' => $teacher->facebook,
                'photo' => $teacher->photoUrl(),
            ],
        ]);
    }

    public function update(TeacherRequest $request, User $teacher, FileStore $files): RedirectResponse
    {
        $teacher->update([
            ...$this->payload($request),
            ...($request->filled('password')
                ? ['password' => Hash::make((string) $request->string('password')->value())]
                : []),
            ...($request->hasFile('photo')
                ? ['photo' => $files->putPublic($request->file('photo'), 'teachers', $teacher->photo)]
                : []),
        ]);

        Toast::success('Perubahan tersimpan.');

        return to_route('admin.teachers.index');
    }

    public function destroy(User $teacher): RedirectResponse
    {
        abort_if($teacher->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');

        $teacher->delete();

        Toast::success('Data guru dihapus.');

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(TeacherRequest $request): array
    {
        return [
            'name' => $request->string('name')->value(),
            'email' => $request->string('email')->value(),
            'position' => $request->string('position')->value(),
            'phone' => $request->string('phone')->value(),
            'bio' => $request->string('bio')->value(),
            'instagram' => $request->string('instagram')->value(),
            'facebook' => $request->string('facebook')->value(),
            'role' => $request->string('role')->value(),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function row(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'position' => $user->position,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ];
    }
}
