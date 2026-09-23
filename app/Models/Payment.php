<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'schedule_id',
        'user_id',
        'agent_id',
        'amount',
        'method',
        'mvola_transaction_id',
        'mvola_phone',
        'status',
        'receipt_path',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Le prêt concerné par ce versement.
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * L'échéance que ce versement vient régler (peut être nulle).
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Le client qui a effectué ce versement.
     * Exemple d'utilisation : $payment->client->name
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * L'agent qui a encaissé ce versement (uniquement si paiement en espèces).
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
