<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['code', 'name', 'sort_order'])]
class Platform extends Model
{
    public function apps(): BelongsToMany
    {
        return $this->belongsToMany(App::class);
    }
}
