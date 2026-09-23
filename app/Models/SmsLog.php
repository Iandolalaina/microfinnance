<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'message',
        'type',
        'status',
        'provider_response',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /**
     * L'utilisateur destinataire de ce SMS (peut être null si numéro inconnu).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
