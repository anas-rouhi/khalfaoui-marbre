<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasTranslations;

    /**
     * Familles de teintes proposées par le filtre du catalogue.
     * Les clés doivent rester identiques à celles de CatalogueSection.vue.
     */
    public const COLOR_FAMILIES = [
        'noir' => 'Noir',
        'blanc' => 'Blanc',
        'gris' => 'Gris',
        'beige' => 'Beige',
        'brun' => 'Brun',
        'vert' => 'Vert',
    ];

    /**
     * Usages proposés par le filtre du catalogue et par l'estimateur de devis.
     * Les clés doivent rester identiques à celles de CatalogueSection.vue
     * et DevisSection.vue.
     */
    public const APPLICATIONS = [
        'sol' => 'Sol',
        'cuisine' => 'Plan de travail / Cuisine',
        'salle-de-bain' => 'Salle de bain',
        'facade' => 'Façade',
        'escalier' => 'Escalier',
    ];

    protected $guarded = [];

    protected $casts = [
        'applications' => 'array',
        'featured' => 'boolean',
        'price_per_m2' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }
}
