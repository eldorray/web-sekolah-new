<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property Carbon|null $visit_date
 * @property int $participants
 * @property string $purpose
 * @property string $status
 */
class VisitSchedule extends Model
{
    /** @var list<string> */
    public const STATUSES = ['pending', 'approved', 'done', 'rejected'];

    protected $fillable = [
        'name', 'email', 'phone', 'visit_date', 'participants', 'purpose', 'status',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'participants' => 'integer',
        ];
    }
}
