<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'token', 'advice'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @return HasMany<Result, $this> */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * The Participant's Results in display order: score descending, then id
     * ascending so ties resolve to the same Love Language everywhere.
     *
     * @return HasMany<Result, $this>
     */
    public function rankedResults(): HasMany
    {
        return $this->results()->with('loveLanguage')->orderByDesc('value')->orderBy('id');
    }
}
