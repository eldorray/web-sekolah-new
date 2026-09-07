<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Response;

class IconController extends Controller
{
    /**
     * Icon names the Svelte Icon component can render.
     *
     * ponytail: kept as a constant instead of a table — the list only changes
     * when a developer adds an import to Icon.svelte.
     *
     * @var list<string>
     */
    private const ICONS = [
        'book-marked', 'book-open', 'calendar-days', 'cpu', 'flask-conical',
        'graduation-cap', 'languages', 'library-big', 'microscope', 'phone',
        'sprout', 'trophy', 'users', 'volleyball',
    ];

    public function __invoke(): Response
    {
        return inertia('admin/Icons', [
            'icons' => self::ICONS,
        ]);
    }
}
