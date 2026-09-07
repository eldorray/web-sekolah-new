<?php

namespace App\Http\Middleware;

use App\Models\Program;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                // Only the fields the UI renders; never the whole model.
                'user' => $user === null ? null : [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'photo_url' => $user->photoUrl(),
                ],
            ],
            'school' => Setting::many([
                'school_name', 'foundation_name', 'address', 'email',
                'phone', 'whatsapp', 'hours', 'instagram', 'facebook', 'youtube',
            ]),
            'branding' => [
                'logo' => Setting::imageUrl('logo'),
                'favicon' => Setting::imageUrl('favicon'),
                'hero_image' => Setting::imageUrl('hero_image'),
                'og_image' => Setting::imageUrl('og_image'),
            ],
            // Footer links; cached because every page renders them.
            'navPrograms' => Cache::remember(
                'nav:programs',
                now()->addHour(),
                fn (): array => Program::query()->active()->take(5)->get(['slug', 'title'])
                    ->map(fn (Program $program): array => [
                        'slug' => $program->slug,
                        'title' => $program->title,
                    ])
                    ->all(),
            ),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
