<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description'])]
class LoveLanguage extends Model
{
    /** @return HasMany<Statement> */
    public function statements(): HasMany
    {
        return $this->hasMany(Statement::class);
    }
}
