<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role',
        'profession',
        'status',
        'deletion_requested_at',
        'deletion_token',
        'deletion_reason',
        'deletion_reason_detail',
    ];

    protected $attributes = [
    'role'   => 'stylist',
    'status' => 'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'deletion_requested_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            // Empêche la suppression si une boutique existe (sauf si on force)
            if (!$user->isForceDeleting() && $user->shop()->exists()) {
                throw new \Exception(
                    'Impossible de supprimer : une boutique est encore associée à ce compte.'
                );
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStylist(): bool
    {
        return $this->role === 'stylist';
    }

    public function isPendingDeletion(): bool
{
    return $this->deletion_requested_at !== null;
}

public function deletionDueAt(): ?\Carbon\Carbon
{
    return $this->deletion_requested_at?->copy()->addDays(30);
}

public function daysUntilDeletion(): ?int
{
    if (!$this->deletion_requested_at) return null;
    return max(0, (int) now()->diffInDays($this->deletionDueAt(), false));
}

public function professionLabel(): string
{
    $map = [
        'stylist'   => ['neutral' => 'Styliste',  'male' => 'Styliste',  'female' => 'Styliste'],
        'tailor'    => ['neutral' => 'Tailleur',  'male' => 'Tailleur',  'female' => 'Tailleuse'],
        'couturier' => ['neutral' => 'Couturier', 'male' => 'Couturier', 'female' => 'Couturière'],
        'creator'   => ['neutral' => 'Créateur',  'male' => 'Créateur',  'female' => 'Créatrice'],
    ];

    return $map[$this->profession][$this->gender] ?? 'Artisan';
}
}
