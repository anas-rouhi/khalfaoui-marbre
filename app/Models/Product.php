<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasTranslations;

    // Teinte et usages sont administrés depuis le back-office :
    // `color_family` → ColorFamily::key, `applications` → InstallationRate::application.

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

    public function colorFamily()
    {
        return $this->belongsTo(ColorFamily::class, 'color_family', 'key');
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
