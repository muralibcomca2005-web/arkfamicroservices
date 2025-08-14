<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveClass extends Model
{
    /** @use HasFactory<\Database\Factories\LiveClassFactory> */
    use HasFactory;

    protected $fillable = ['course_id', 'topic', 'meeting_link', 'scheduled_at', 'class_ended'];

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function attendance() {
        return $this->hasMany(Attendance::class);
    }
}
