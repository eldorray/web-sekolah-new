<?php

declare(strict_types=1);

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Services\FileStore;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return inertia('guru/Profile', [
            'profile' => [
                'name' => $user->name,
                'position' => $user->position,
                'phone' => $user->phone,
                'bio' => $user->bio,
                'instagram' => $user->instagram,
                'facebook' => $user->facebook,
                'photo' => $user->photoUrl(),
            ],
        ]);
    }

    public function update(Request $request, FileStore $files): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:240'],
            'instagram' => ['nullable', 'url', 'max:200'],
            'facebook' => ['nullable', 'url', 'max:200'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user->update([
            ...Arr::except($validated, 'photo'),
            ...($request->hasFile('photo')
                ? ['photo' => $files->putPublic($request->file('photo'), 'teachers', $user->photo)]
                : []),
        ]);

        Toast::success('Profil tersimpan.');

        return back();
    }
}
