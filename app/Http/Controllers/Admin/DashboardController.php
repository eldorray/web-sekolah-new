<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\News;
use App\Models\PpdbRegistration;
use App\Services\VisitorStats;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(VisitorStats $stats): Response
    {
        $today = $stats->today();
        $yesterday = $stats->yesterday();
        $pending = PpdbRegistration::query()->where('status', 'pending')->count();
        $drafts = News::query()->where('is_published', false)->count();
        $unread = ContactMessage::query()->where('is_read', false)->count();

        return inertia('admin/Dashboard', [
            'summary' => [
                [
                    'key' => 'visits',
                    'label' => 'Pengunjung hari ini',
                    'value' => (string) $today,
                    'hint' => $this->trend($today, $yesterday),
                ],
                [
                    'key' => 'ppdb',
                    'label' => 'Pendaftar menunggu verifikasi',
                    'value' => (string) $pending,
                    'hint' => PpdbRegistration::query()->whereDate('created_at', today())->count().' masuk hari ini',
                ],
                [
                    'key' => 'news',
                    'label' => 'Berita terbit',
                    'value' => (string) News::query()->where('is_published', true)->count(),
                    'hint' => $drafts.' draf menunggu',
                ],
                [
                    'key' => 'messages',
                    'label' => 'Pesan belum dibaca',
                    'value' => (string) $unread,
                    'hint' => $unread === 0 ? 'Kotak masuk bersih' : 'Perlu dibalas',
                ],
            ],
            'visitorSeries' => $stats->dailySeries(),
            'topCountries' => $stats->topCountries(),
            'registrations' => PpdbRegistration::query()->latest()->take(6)->get()
                ->map(fn (PpdbRegistration $row): array => [
                    'number' => $row->registration_number,
                    'full_name' => $row->full_name,
                    'grade_target' => $row->grade_target,
                    'created_at' => $row->created_at?->translatedFormat('j F Y'),
                    'status' => $row->status,
                ])
                ->all(),
            'messages' => ContactMessage::query()->where('is_read', false)->latest()->take(4)->get()
                ->map(fn (ContactMessage $row): array => [
                    'id' => $row->id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'subject' => $row->subject,
                    'created_at' => $row->created_at?->translatedFormat('j F Y'),
                ])
                ->all(),
        ]);
    }

    private function trend(int $today, int $yesterday): string
    {
        if ($yesterday === 0) {
            return 'Belum ada pembanding kemarin';
        }

        $change = (int) round((($today - $yesterday) / $yesterday) * 100);

        return sprintf('%s%d%% dari kemarin', $change >= 0 ? '+' : '', $change);
    }
}
