<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\PpdbRegistration;
use Illuminate\Support\Collection;
use RuntimeException;

class PpdbCsv
{
    /** @var list<string> */
    private const COLUMNS = [
        'registration_number', 'full_name', 'nickname', 'gender', 'birthplace',
        'birthdate', 'previous_school', 'address', 'father_name', 'mother_name',
        'parent_phone', 'parent_email', 'grade_target', 'status', 'notes', 'created_at',
    ];

    /** @param  Collection<int, PpdbRegistration>  $registrations */
    public function build(Collection $registrations): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new RuntimeException('Gagal menyiapkan berkas CSV.');
        }

        fputcsv($handle, self::COLUMNS);

        foreach ($registrations as $registration) {
            fputcsv($handle, array_map(
                fn (string $column): string => (string) $registration->getAttribute($column),
                self::COLUMNS,
            ));
        }

        rewind($handle);
        $csv = (string) stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
