<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

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

        $courses = DB::table('courses')->where('teacher_id', $teacher->user_id)->get();
        $courseIds = $courses->pluck('id')->values()->all();
        $courseContent = DB::table('course_contents')->whereIn('course_id', $courseIds)->orderBy('order')->get();
        $contentsByCourseId = collect($courseContent)->groupBy('course_id');

        $out = [];
        foreach ($courses as $c) {
            $out[] = [
                'id' => $c->id,
                'title' => $c->title,
                'short_description' => $c->short_description,
                'description' => $c->description,
                'teacher_id' => $c->teacher_id,
                'category' => $c->category,
                'price' => $c->price,
                'created_at' => $c->created_at,
                'updated_at' => $c->updated_at,
                'course_content' => array_values(array_map(function ($cc) {
                    return [
                        'id' => $cc->id,
                        'course_id' => $cc->course_id,
                        'title' => $cc->title,
                        'body' => $cc->body,
                        'order' => $cc->order,
                        'created_at' => $cc->created_at,
                        'updated_at' => $cc->updated_at,
                    ];
                }, ($contentsByCourseId[$c->id] ?? collect())->all())),
            ];
        }

        return response()->json([
            'message' => 'Assigned Course retrieved',
            'data' => $out
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

        $courseIds = DB::table('courses')->where('teacher_id', $teacher->user_id)->pluck('id')->values()->all();
        if (empty($courseIds)) {
            return response()->json([
                'message' => 'Assigned students retrieved',
                'data' => []
            ]);
        }

        $enrollments = DB::table('enrollments')->whereIn('course_id', $courseIds)->get();

        $studentIds = $enrollments->pluck('student_id')->unique()->values()->all();
        $studentsById = DB::table('users')->whereIn('id', $studentIds)->get()->keyBy('id');
        $studentProfiles = DB::table('students')->whereIn('user_id', $studentIds)->get()->keyBy('user_id');

        $courses = DB::table('courses')->whereIn('id', $courseIds)->get();
        $courseContent = DB::table('course_contents')->whereIn('course_id', $courseIds)->orderBy('order')->get();
        $contentsByCourseId = collect($courseContent)->groupBy('course_id');
        $coursesById = [];
        foreach ($courses as $c) {
            $coursesById[$c->id] = [
                'id' => $c->id,
                'title' => $c->title,
                'short_description' => $c->short_description,
                'description' => $c->description,
                'teacher_id' => $c->teacher_id,
                'category' => $c->category,
                'price' => $c->price,
                'created_at' => $c->created_at,
                'updated_at' => $c->updated_at,
                'course_content' => array_values(array_map(function ($cc) {
                    return [
                        'id' => $cc->id,
                        'course_id' => $cc->course_id,
                        'title' => $cc->title,
                        'body' => $cc->body,
                        'order' => $cc->order,
                        'created_at' => $cc->created_at,
                        'updated_at' => $cc->updated_at,
                    ];
                }, ($contentsByCourseId[$c->id] ?? collect())->all())),
            ];
        }

        $out = [];
        foreach ($enrollments as $enr) {
            $user = isset($studentsById[$enr->student_id]) ? (array) $studentsById[$enr->student_id] : null;
            if ($user) {
                $user['student'] = isset($studentProfiles[$enr->student_id]) ? (array) $studentProfiles[$enr->student_id] : null;
            }
            $out[] = [
                'id' => $enr->id,
                'student_id' => $enr->student_id,
                'course_id' => $enr->course_id,
                'status' => $enr->status,
                'completion_status' => $enr->completion_status,
                'enrolled_at' => $enr->enrolled_at,
                'created_at' => $enr->created_at,
                'updated_at' => $enr->updated_at,
                'user' => $user,
                'course' => $coursesById[$enr->course_id] ?? null,
            ];
        }

        return response()->json([
            'message' => 'Assigned students retrieved',
            'data' => $out
        ]);
    }
}
