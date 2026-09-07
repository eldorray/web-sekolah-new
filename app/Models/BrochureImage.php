<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BrochureImage extends Model
{
    protected $fillable = ['brochure_id', 'image', 'order'];

    protected function casts(): array
    {
        return ['order' => 'integer'];
    }

    /** @return BelongsTo<Brochure, $this> */
    public function brochure(): BelongsTo
    {
        return $this->belongsTo(Brochure::class);
    }

    public function imageUrl(): string
    {
        return Storage::disk('public')->url($this->image);
    }
}
