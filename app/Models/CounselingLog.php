<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CounselingLog extends Model
{
    protected $fillable = ['student_id', 'user_id', 'notes'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
