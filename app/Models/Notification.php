<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Crée une notification facilement.
     */
    public static function notify(User $user, string $type, string $title, ?string $body = null, ?array $data = null): self
{
    return self::create([
        'user_id' => $user->id,
        'type'    => $type,
        'title'   => $title,
        'body'    => $body,
        'data'    => $data,
    ]);
}
}
