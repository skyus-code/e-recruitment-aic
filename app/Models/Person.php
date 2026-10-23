<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Person extends Model
{
    protected $table = 'persons';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detailPerson(): HasOne
    {
        return $this->hasOne(DetailPerson::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(PersonEducation::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(PersonExperience::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(PersonSkill::class);
    }
}
