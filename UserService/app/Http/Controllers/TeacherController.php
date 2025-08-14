<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;

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

        $courseServiceBase = config('services.courses.url');

        $courses = [];
        if ($courseServiceBase) {
            $res = Http::timeout(5)->get(rtrim($courseServiceBase, '/').'/api/teacher-courses/'.$teacher->user_id);
            if ($res->successful()) {
                $courses = $res->json('data') ?? [];
            }
        }

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

        $courseServiceBase = config('services.courses.url');
        $enrollmentServiceBase = config('services.enrollments.url');

        $enrollments = [];
        if ($courseServiceBase && $enrollmentServiceBase) {
            $coursesRes = Http::timeout(5)->get(rtrim($courseServiceBase, '/').'/api/teacher-courses/'.$teacher->user_id);
            if ($coursesRes->successful()) {
                $courseIds = collect($coursesRes->json('data') ?? [])->pluck('id')->values()->all();
                if (!empty($courseIds)) {
                    $enrollRes = Http::timeout(5)->get(rtrim($enrollmentServiceBase, '/').'/api/enrollments/by-course-ids', [
                        'ids' => $courseIds
                    ]);
                    if ($enrollRes->successful()) {
                        $enrollments = $enrollRes->json('data') ?? [];
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Assigned students retrieved',
            'data' => $enrollments
        ]);
    }
}
