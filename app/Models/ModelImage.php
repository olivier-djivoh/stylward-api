<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelImage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'model_id',
        'path',
        'type',
        'sort_order',
        'alt_text',
        'size_bytes',
        'mime_type',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(ClothingModel::class, 'model_id');
    }
}
