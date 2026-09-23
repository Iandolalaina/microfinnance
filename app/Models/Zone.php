<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Tous les utilisateurs (clients/agents) rattachés à cette zone.
     * Exemple d'utilisation : $zone->users
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Les annonces ciblées spécifiquement sur cette zone.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }
}
