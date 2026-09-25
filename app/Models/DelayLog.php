<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DelayLog extends Model
{
    protected $fillable = [
        'student_id',
        'delay_time',
        'reason',
        'unique_code',
        'status',
        'reporter_name',
    ];

    protected $casts = [
        'delay_time' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
