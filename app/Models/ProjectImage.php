<?php

namespace App\Models;

use App\Models\Concerns\GeneratesWebpCopies;
use Illuminate\Database\Eloquent\Model;

class ProjectImage extends Model
{
    use GeneratesWebpCopies;

    protected $guarded = [];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    protected function webpImageAttributes(): array
    {
        return ['image_path'];
    }
}
