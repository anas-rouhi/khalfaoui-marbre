<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devis extends Model
{
    protected $table = 'devis';

    protected $guarded = [];

    protected $casts = [
        'surface_m2' => 'decimal:2',
        'estimated_total' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
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
