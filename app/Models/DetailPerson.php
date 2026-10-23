<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPerson extends Model
{
    protected $table = 'detail_persons';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
