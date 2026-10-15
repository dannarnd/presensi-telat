<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DelayLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'delay_time',
        'reason',
        'unique_code',
        'status',
        'reporter_name',
        'deleter_name',
        'deletion_reason',
        'deletion_ip',
        'deletion_user_agent',
        'deletion_device',
        'deleted_by_user_id',
    ];

    protected $casts = [
        'delay_time' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function deletedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }
}
