<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = ['nisn', 'name', 'school_class_id', 'no_wa_ortu', 'status'];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function delayLogs(): HasMany
    {
        return $this->hasMany(DelayLog::class);
    }

    public function activeDelayLogs(): HasMany
    {
        return $this->hasMany(DelayLog::class)->where('status', 'active');
    }

    public function counselingLogs(): HasMany
    {
        return $this->hasMany(CounselingLog::class);
    }
}
