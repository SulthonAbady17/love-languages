<?php

namespace App\Models;

use Database\Factories\LoveLanguageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description'])]
class LoveLanguage extends Model
{
    /** @use HasFactory<LoveLanguageFactory> */
    use HasFactory;

    /** @return HasMany<Statement> */
    public function statements(): HasMany
    {
        return $this->hasMany(Statement::class);
    }
}
