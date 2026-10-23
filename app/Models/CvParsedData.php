<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvParsedData extends Model
{
    protected $table = 'cv_parsed_data';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'parsed_at' => 'datetime',
        ];
    }

    public function cvFile(): BelongsTo
    {
        return $this->belongsTo(CvFile::class, 'cv_file_id');
    }
}
