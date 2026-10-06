<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DeletedAccountArchive extends Model
{
    use HasUuids;

    protected $table = 'deleted_accounts_archive';

    protected $fillable = [
        'email_hash',
        'shop_name',
        'shop_slug',
        'shop_views_total',
        'shop_visitors_total',
        'shop_whatsapp_clicks',
        'models_count',
        'published_models_count',
        'deletion_reason',
        'deletion_reason_detail',
        'registered_at',
        'deletion_requested_at',
        'deleted_at',
    ];

    protected $casts = [
        'registered_at'         => 'datetime',
        'deletion_requested_at' => 'datetime',
        'deleted_at'            => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Ajoute les colonnes de suppression au modèle User.
     */
    public static function reasonLabels(): array
    {
        return [
            'no_longer_needed' => 'Je n\'en ai plus besoin',
            'too_expensive'    => 'Trop cher',
            'missing_features' => 'Fonctionnalités manquantes',
            'switched_tool'    => 'Je suis passé à un autre outil',
            'bug'              => 'Problème technique / Bug',
            'other'            => 'Autre raison',
        ];
    }

    public function reasonLabel(): string
    {
        return self::reasonLabels()[$this->deletion_reason] ?? 'Non précisé';
    }
}
