<?php

namespace App\Models;

use Database\Factories\StatementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['love_language_id', 'body'])]
class Statement extends Model
{
    /** @use HasFactory<StatementFactory> */
    use HasFactory;

    /** @return BelongsTo<LoveLanguage, $this> */
    public function loveLanguage(): BelongsTo
    {
        return $this->belongsTo(LoveLanguage::class);
    }
}
