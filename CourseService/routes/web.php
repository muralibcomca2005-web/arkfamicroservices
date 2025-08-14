<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('course-service')->middleware(['auth:sanctum'])->group(function () {
    Route::controller(CourseController::class)->prefix('courses')->name('courses.')->group(function () {
        Route::post('/', 'addCourse')->name('store');
        Route::patch('/{course}/assign-teacher', 'assignTeachers')->name('assign-teacher');
        Route::patch('/{course}/remove-teacher', 'removeAssignedTeacher')->name('remove-teacher');
        Route::get('/', 'fetchCourse')->name('index');
    });
});
