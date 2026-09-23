<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'zone_id',
        'created_by',
        'send_sms',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'send_sms' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * La zone géographique ciblée par cette annonce (null = générale).
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * L'auteur (admin ou agent) qui a publié cette annonce.
     * Exemple d'utilisation : $announcement->author->name
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
