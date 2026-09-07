<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitSchedule;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Response;

class VisitController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/Visits', [
            'visits' => VisitSchedule::query()->orderByDesc('visit_date')->get()
                ->map(fn (VisitSchedule $row): array => [
                    'id' => $row->id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'phone' => $row->phone,
                    'visit_date' => $row->visit_date?->toDateString(),
                    'participants' => $row->participants,
                    'purpose' => $row->purpose,
                    'status' => $row->status,
                ])
                ->all(),
        ]);
    }

    public function update(Request $request, VisitSchedule $visit): RedirectResponse
    {
        $visit->update($request->validate([
            'status' => ['required', Rule::in(VisitSchedule::STATUSES)],
        ]));

        Toast::success('Status kunjungan diperbarui.');

        return back();
    }
}
