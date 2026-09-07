<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Records create/update/delete events against the acting user.
 *
 * @mixin Model
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (Model $model) => self::recordAudit($model, 'created', $model->getAttributes()));
        static::updated(fn (Model $model) => self::recordAudit($model, 'updated', $model->getChanges()));
        static::deleted(fn (Model $model) => self::recordAudit($model, 'deleted', []));
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private static function recordAudit(Model $model, string $event, array $changes): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'changes' => array_diff_key($changes, array_flip(['updated_at', 'created_at'])),
        ]);
    }
}
