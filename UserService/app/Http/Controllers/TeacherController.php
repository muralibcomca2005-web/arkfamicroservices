<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Enrollment;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeacherController extends Controller
{
    public function addTeacher(Request $request)
    {
        if (!Gate::allows('only-admin')) {
            return response()->json([
                'message' => 'Unauthorized User',
            ], 403);
        }
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
            'qualification' => 'required|string',
            'department' => 'required|string',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::TEACHER,
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'emp_id' => Teacher::empId(),
            'qualification' => $data['qualification'],
            'department' => $data['department'],
        ]);

        return response()->json([
            'message' => 'User with Teacher role created',
            'data' => ['user' => $user, 'teacher' => $teacher],
        ], 201);
    }

    public function fetchTeachers()
    {
        $users = User::where('role', UserRole::TEACHER)->get();

        if (!$users) {
            return response()->json([
                'message' => 'No Teachers found'
            ], 404);
        }

        return response()->json([
            'message' => 'Users with Teachers role retrieved Successfully',
            'data' => $users->load('teacher')
        ], 200);
    }

    public function fetchAssignedCourses($id)
    {
        $teacher = Teacher::where('user_id', $id)->first();
        if (!$teacher) {
            return response()->json([
                'message' => 'No Teachers found'
            ], 404);
        }

        $courses = $teacher->courses()->with('courseContent')->get();

        return response()->json([
            'message' => 'Assigned Course retrieved',
            'data' => $courses
        ]);
    }

    public function fetchAssignedStudents($id)
    {
        $teacher = Teacher::where('user_id', $id)->first();

        if (!$teacher) {
            return response()->json([
                'message' => 'No teacher found',
            ], 404);
        }

        $courseId = $teacher->courses()->pluck('id');

        $enrollments = Enrollment::with(['user.student', 'course'])->whereIn('course_id', $courseId)->get();

        return response()->json([
            'message' => 'Assigned students retrieved',
            'data' => $enrollments
        ]);
    }
}
