<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'love_language_id', 'value'])]
class Result extends Model
{
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<LoveLanguage, $this> */
    public function loveLanguage(): BelongsTo
    {
        return $this->belongsTo(LoveLanguage::class);
    }
}
