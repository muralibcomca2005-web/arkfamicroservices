<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'emp_id',
        'qualification',
        'department',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id', 'user_id');
    }

    public static function empId()
    {
        $year = now()->year;

        $count = Teacher::whereYear('created_at', $year)->count() + 1;

        $serial = str_pad($count, 4, '0', STR_PAD_LEFT);

        return 'EMP' . $year . $serial;
    }
}
