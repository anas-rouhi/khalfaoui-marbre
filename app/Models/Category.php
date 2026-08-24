<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}