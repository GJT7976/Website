<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'sort_order'])]
class AppCategory extends Model
{
    public function apps(): HasMany
    {
        return $this->hasMany(App::class, 'category_id');
    }
}
