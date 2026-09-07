<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string $role
 * @property string|null $phone
 * @property string|null $position
 * @property string|null $bio
 * @property string|null $photo
 * @property string|null $instagram
 * @property string|null $facebook
 * @property bool $is_active
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name', 'email', 'password', 'role', 'phone', 'position',
    'bio', 'photo', 'instagram', 'facebook', 'is_active',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<News, $this> */
    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    /** Uploaded photo when present, otherwise a generated initials avatar. */
    public function photoUrl(): string
    {
        if ($this->photo !== null && $this->photo !== '') {
            return str_starts_with($this->photo, 'http')
                ? $this->photo
                : Storage::disk('public')->url($this->photo);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=0f7b6c&color=fff';
    }

    /** @param  Builder<self>  $query */
    public function scopeActiveTeachers(Builder $query): void
    {
        $query->where('role', 'guru')->where('is_active', true)->orderBy('name');
    }
}
