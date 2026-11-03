<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devis extends Model
{
    protected $table = 'devis';

    /**
     * Champs alimentés par le formulaire public.
     *
     * `status` en est volontairement absent : un visiteur ne doit pas pouvoir
     * classer lui-même sa demande. Le back-office passe par `markAs()`.
     */
    protected $fillable = [
        'client_name',
        'phone',
        'email',
        'location',
        'product_id',
        'application',
        'surface_m2',
        'estimated_total',
        'message',
    ];

    protected $casts = [
        'surface_m2' => 'decimal:2',
        'estimated_total' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** Change le statut de suivi, hors assignation de masse. */
    public function markAs(string $status): bool
    {
        return $this->forceFill(['status' => $status])->save();
    }

    /** Référence lisible portée par le devis PDF, ex. « DEV-2026-0042 ». */
    public function getReferenceAttribute(): string
    {
        return sprintf(
            'DEV-%s-%04d',
            ($this->created_at ?? now())->format('Y'),
            $this->getKey() ?? 0
        );
    }
}
