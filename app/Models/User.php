<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Champs autorisés au remplissage massif (mass assignment)
     * via User::create([...]) par exemple.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'pin',
        'role',
        'zone_id',
        'address',
        'is_active',
    ];

    /**
     * Champs jamais renvoyés dans un tableau/JSON (ex: réponse API).
     * Sécurité : on ne veut jamais exposer le mot de passe ou le PIN.
     */
    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    /**
     * Conversion automatique de types quand on lit/écrit ces champs.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // hash automatique à l'enregistrement
            'is_active' => 'boolean',
        ];
    }

    // ------------------------------------------------------------
    // RELATIONS
    // ------------------------------------------------------------

    /**
     * La zone géographique à laquelle cet utilisateur appartient.
     * Exemple d'utilisation : $user->zone->name
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Les prêts dont CET utilisateur est le CLIENT.
     * Exemple d'utilisation : $user->loans (retourne une collection de Loan)
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'user_id');
    }

    /**
     * Les prêts dont CET utilisateur est l'AGENT en charge (suivi).
     * Exemple d'utilisation : $agent->loansManaged
     */
    public function loansManaged(): HasMany
    {
        return $this->hasMany(Loan::class, 'agent_id');
    }

    /**
     * Les versements effectués par CET utilisateur en tant que CLIENT.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    /**
     * Les versements ENCAISSÉS EN ESPÈCES par CET utilisateur en tant qu'AGENT.
     */
    public function paymentsCollected(): HasMany
    {
        return $this->hasMany(Payment::class, 'agent_id');
    }

    /**
     * Les annonces publiées par CET utilisateur (admin/agent).
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    /**
     * L'historique des SMS envoyés à CET utilisateur.
     */
    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class, 'user_id');
    }

    // ------------------------------------------------------------
    // PETITS HELPERS PRATIQUES (facultatif mais utile plus tard)
    // ------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }
}
