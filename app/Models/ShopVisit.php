<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopVisit extends Model
{
    use HasUuids;

    protected $fillable = [
        'shop_id',
        'model_id',
        'event',
        'ip_hash',
        'user_agent',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(ClothingModel::class, 'model_id');
    }
}
