<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/Users/Index', [
            'users' => User::query()->orderBy('name')->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                ])
                ->all(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/Users/Form');
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create([
            ...$request->safe()->except('password'),
            'password' => Hash::make((string) $request->string('password')->value()),
        ]);

        Toast::success('Akun dibuat.');

        return to_route('admin.users.index');
    }

    public function edit(User $user): Response
    {
        return inertia('admin/Users/Form', [
            'id' => (string) $user->id,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            ...$request->safe()->except('password'),
            ...($request->filled('password')
                ? ['password' => Hash::make((string) $request->string('password')->value())]
                : []),
        ]);

        Toast::success('Perubahan tersimpan.');

        return to_route('admin.users.index');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');

        $user->delete();

        Toast::success('Akun dihapus.');

        return back();
    }

    public function sendPasswordReset(User $user): RedirectResponse
    {
        Password::sendResetLink(['email' => $user->email]);

        Toast::success("Tautan reset password dikirim ke {$user->email}.");

        return back();
    }
}
