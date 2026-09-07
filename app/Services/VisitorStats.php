<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\VisitorLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VisitorStats
{
    /**
     * Daily visit counts for the last N days, zero-filled.
     *
     * @return list<array{date: string, visits: int}>
     */
    public function dailySeries(int $days = 14): array
    {
        $since = Carbon::today()->subDays($days - 1);

        $counts = VisitorLog::query()
            ->where('created_at', '>=', $since)
            ->select([DB::raw('date(created_at) as day'), DB::raw('count(*) as total')])
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $date = $since->copy()->addDays($offset);

            $series[] = [
                'date' => $date->translatedFormat('j M'),
                'visits' => (int) ($counts[$date->toDateString()] ?? 0),
            ];
        }

        return $series;
    }

    /**
     * @return list<array{country: string, code: string, visits: int}>
     */
    public function topCountries(int $limit = 5, int $days = 30): array
    {
        $rows = VisitorLog::query()
            ->where('created_at', '>=', Carbon::today()->subDays($days))
            ->whereNotNull('country')
            ->select(['country', 'country_code', DB::raw('count(*) as total')])
            ->groupBy('country', 'country_code')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->all();

        return array_values(array_map(
            fn (VisitorLog $log): array => [
                'country' => (string) $log->country,
                'code' => (string) $log->country_code,
                'visits' => (int) $log->getAttribute('total'),
            ],
            $rows,
        ));
    }

    public function today(): int
    {
        return VisitorLog::query()->whereDate('created_at', Carbon::today())->count();
    }

    public function yesterday(): int
    {
        return VisitorLog::query()->whereDate('created_at', Carbon::yesterday())->count();
    }
}
