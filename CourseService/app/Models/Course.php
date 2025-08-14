<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;

    protected $fillable = ['title', 'short_description', 'description', 'teacher_id', 'category', 'price'];

    public function courseContent()
    {
        return $this->hasMany(CourseContent::class);
    }
}
