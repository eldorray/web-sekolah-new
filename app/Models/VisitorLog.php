<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'session_id', 'ip', 'country', 'country_code', 'path', 'user_agent',
    ];
}
