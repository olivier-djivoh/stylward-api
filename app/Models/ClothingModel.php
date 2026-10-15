<?php

namespace App\Models;

use App\Models\Scopes\ShopScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClothingModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'models';

    protected $fillable = [
        'shop_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'color',
        'reference',
        'status',
        'published_at',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ShopScope());
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
{
    return $this->hasMany(ModelImage::class, 'model_id')
        ->orderByRaw("CASE WHEN type = 'main' THEN 0 ELSE 1 END")
        ->orderBy('sort_order');
}

public function collections(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
{
    return $this->belongsToMany(
        Collection::class,
        'collection_model',
        'model_id',
        'collection_id'
    )->withPivot('sort_order');
}
}
