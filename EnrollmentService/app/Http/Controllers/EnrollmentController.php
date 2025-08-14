<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    public function enrollRequest($courseId, $stu_id)
    {
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json([
                'message' => 'Course is not found'
            ], 404);
        }
        $exists = Enrollment::where('student_id', $stu_id)->where('course_id', $courseId)->exists();

        if ($exists) {
            return response()->json([
                'message' => 'You are already enrolled in this course '
            ], 409);
        }

        $enroll = Enrollment::create([
            'student_id' => $stu_id,
            'course_id' => $courseId,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Enrolled Request created',
            'data' => $enroll
        ], 201);
    }

    public function enrollStudent($id)
    {
        $enrollment = Enrollment::findOrFail($id);

        $enrollment->update([
            'status' => 'enrolled',
            'completion_status' => 'in_progress',
            'enrolled_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Enrolled student Successfully'
        ], 200);
    }

    public function fetchPendingRequest($id)
    {
        $enroll = Enrollment::where('student_id', $id)->where('status', 'pending')->with(['user.student', 'course.courseContent'])->get();
        if ($enroll->isEmpty()) {
            return response()->json([
                'message' => "There is no pending course requests"
            ], 404);
        }

        return response()->json([
            'message' => 'Pending Courses',
            'data' => $enroll,
        ]);
    }

    public function fetchAllPendingRequest()
    {
        $enroll = Enrollment::where('status', 'pending')->with(['user.student', 'course.courseContent'])->get();
        if ($enroll->isEmpty()) {
            return response()->json([
                'message' => "There is no pending course requests"
            ], 404);
        }

        return response()->json([
            'message' => 'Pending Courses',
            'data' => $enroll,
        ]);
    }

    public function rejectPendingRequest($id)
    {
        $enroll = Enrollment::findOrFail($id);
        $enroll->delete();

        return response()->json([
            'message' => 'Pending rejected successfully'
        ], 200);
    }

    public function fetchEnrolledCourse($id)
    {
        $enroll = Enrollment::where('student_id', $id)->where('status', 'enrolled')->get();
        if ($enroll->isEmpty()) {
            return response()->json([
                'message' => "Student did'nt enrolled a course"
            ], 404);
        }

        $courseIds = $enroll->pluck('course_id')->unique();
        $courses = Course::whereIn('id', $courseIds)->with('courseContent')->get();

        return response()->json([
            'message' => 'Enrolled Courses',
            'data' => $enroll,
            'courses' => $courses
        ]);
    }

    public function getEnrolledCoursesForStudent($stuId)
    {
        $enrollmentRecords = Enrollment::where('student_id', $stuId)->where('status', 'enrolled')->get();

        if ($enrollmentRecords->isEmpty()) {
            return response()->json(['data' => []], 200);
        }

        $courseIds = $enrollmentRecords->pluck('course_id')->unique();
        $courses = Course::whereIn('id', $courseIds)->get();

        return response()->json(['data' => $courses], 200);
    }
}
