<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reg_no',
        'user_id',
        'department',
        'selected_course',
        'date_of_birth',
        'gender',
        'address',
        'phone',
        'country',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function regNo()
    {
        $year = now()->year;

        $count = Student::whereYear('created_at', $year)->count() + 1;

        $serial = str_pad($count, 4, '0', STR_PAD_LEFT);

        return 'REG' . $year . $serial;
    }
}
