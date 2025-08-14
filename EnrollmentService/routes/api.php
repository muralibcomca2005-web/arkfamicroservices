<?php

use App\Http\Controllers\EnrollmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/enrolled-courses/{studentId}', [EnrollmentController::class, 'getEnrolledCoursesForStudent']);
Route::get('/enrollments/by-course-ids', [EnrollmentController::class, 'getEnrollmentsByCourseIds']);
