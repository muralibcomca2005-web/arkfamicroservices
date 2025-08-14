<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    protected $fillable = ['student_id', 'live_class_id', 'status', 'verified', 'join_time', 'verified_time', 'verified_by', 'leave_time'];
}
