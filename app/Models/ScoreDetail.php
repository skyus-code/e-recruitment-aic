<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreDetail extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_matched' => 'boolean',
            'matched_score' => 'decimal:2',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function jobKeyword(): BelongsTo
    {
        return $this->belongsTo(JobKeyword::class);
    }
}
