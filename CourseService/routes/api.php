<?php

use App\Http\Controllers\CourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/courses', [CourseController::class, 'getCourseByIds']);
Route::get('/teacher-courses/{tchId}', [CourseController::class, 'getCoursesByTeacher']);
