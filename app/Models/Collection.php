<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'name',
        'slug',
        'description',
        'cover_path',
        'status',
        'published_at',
        'sort_order',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function models(): BelongsToMany
{
    return $this->belongsToMany(
        ClothingModel::class,
        'collection_model',
        'collection_id',
        'model_id'
    )
        ->withPivot('sort_order', 'created_at')
        ->orderBy('collection_model.sort_order');
}
}
