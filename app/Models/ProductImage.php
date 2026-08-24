<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Une seule photo principale par produit : cocher la case sur une image
        // décoche automatiquement les autres. `update()` ne redéclenche pas
        // d'événement, la mise à jour ne boucle donc pas.
        static::saved(function (ProductImage $image) {
            if (! $image->is_main) {
                return;
            }

            static::query()
                ->where('product_id', $image->product_id)
                ->whereKeyNot($image->getKey())
                ->where('is_main', true)
                ->update(['is_main' => false]);
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
