<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseContent extends Model
{
    /** @use HasFactory<\Database\Factories\CourseContentFactory> */
    use HasFactory;

    protected $fillable = ['course_id', 'title', 'body', 'order'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
