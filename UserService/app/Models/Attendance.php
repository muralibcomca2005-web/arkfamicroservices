<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function liveClass()
    {
        return $this->belongsTo(LiveClass::class);
    }
}