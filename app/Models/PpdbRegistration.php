<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $registration_number
 * @property string $full_name
 * @property string|null $nickname
 * @property string $gender
 * @property string $birthplace
 * @property Carbon|null $birthdate
 * @property string $previous_school
 * @property string $address
 * @property string $father_name
 * @property string $mother_name
 * @property string $parent_phone
 * @property string $parent_email
 * @property string $grade_target
 * @property string|null $kk_file
 * @property string|null $birth_certificate_file
 * @property string $status
 * @property string|null $notes
 */
class PpdbRegistration extends Model
{
    use Auditable;
    use SoftDeletes;

    /** @var list<string> */
    public const STATUSES = ['pending', 'accepted', 'rejected'];

    protected $fillable = [
        'registration_number', 'full_name', 'nickname', 'gender', 'birthplace',
        'birthdate', 'previous_school', 'address', 'father_name', 'mother_name',
        'parent_phone', 'parent_email', 'grade_target', 'kk_file',
        'birth_certificate_file', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return ['birthdate' => 'date'];
    }

    /**
     * Create a registration with a collision-safe number.
     *
     * The number is taken inside a transaction with a row lock so two
     * simultaneous submissions cannot claim the same sequence.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function createWithNumber(array $attributes): self
    {
        $year = now()->year;

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return DB::transaction(function () use ($attributes, $year): self {
                    $last = self::withTrashed()
                        ->where('registration_number', 'like', "PPDB-{$year}-%")
                        ->lockForUpdate()
                        ->orderByDesc('registration_number')
                        ->value('registration_number');

                    $sequence = $last === null
                        ? 1
                        : ((int) substr((string) $last, -4)) + 1;

                    return self::create([
                        ...$attributes,
                        'registration_number' => sprintf('PPDB-%d-%04d', $year, $sequence),
                    ]);
                });
            } catch (QueryException $exception) {
                if ($attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new \RuntimeException('Nomor pendaftaran gagal dibuat.');
    }
}
