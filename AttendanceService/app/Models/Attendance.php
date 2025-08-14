<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    protected $fillable = ['student_id', 'live_class_id', 'status', 'verified', 'join_time', 'verified_time', 'verified_by', 'leave_time'];

    public function user()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    public function liveClass()
    {
        return $this->belongsTo(LiveClass::class);
    }
    public function teacher()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
