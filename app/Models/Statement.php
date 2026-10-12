<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['love_language_id', 'body'])]
class Statement extends Model
{
    /** @return BelongsTo<LoveLanguage, $this> */
    public function loveLanguage(): BelongsTo
    {
        return $this->belongsTo(LoveLanguage::class);
    }
}
