<?php

namespace App\Models;

use App\Enums\KeywordCategoryEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobKeyword extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'category' => KeywordCategoryEnum::class,
            'weight' => 'integer',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }
}
