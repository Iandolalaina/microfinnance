<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $placeNames = collect([$user->region, $user->fokontany])
            ->filter()
            ->flatMap(function (string $name) {
                $normalized = mb_strtolower(trim($name));
                $withoutPrefix = preg_replace('/^fokontany\s+/u', '', $normalized);

                return [$normalized, $withoutPrefix, 'fokontany '.$withoutPrefix];
            })
            ->unique()
            ->values()
            ->all();

        return $query->where(function (Builder $query) use ($user, $placeNames) {
            $query->whereNull('announcements.zone_id');

            if ($user->zone_id) {
                $query->orWhere('announcements.zone_id', $user->zone_id);
            }

            if ($placeNames !== []) {
                $query->orWhereHas('zone', fn (Builder $zoneQuery) =>
                    $zoneQuery->whereIn(DB::raw('LOWER(zones.name)'), $placeNames)
                );
            }
        });
    }
}
