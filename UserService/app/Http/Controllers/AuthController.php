<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function  login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        $remember = $request->boolean('remember');

        // Check if email exists
        if (!User::where('email', $credentials['email'])->exists()) {
            return response()->json([
                'message' => 'Email not registered',
                'errors' => [
                    'email' => ['This email address is not registered.']
                ]
            ], 404);
        }

        // Attempt login
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return response()->json([
                'message' => 'Login Success',
                'user' => Auth::user()->load('student', 'teacher')
            ], 200);
        }

        // Invalid credentials
        return response()->json([
            'message' => 'Invalid Credentials',
        ], 422);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged Out Successfully'
        ], 200);
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        $coursesData = [];

        try {
            $rows = DB::select(<<<'SQL'
                select
                    c.id, c.title, c.short_description, c.description, c.teacher_id, c.category, c.price, c.created_at, c.updated_at,
                    coalesce(
                        json_agg(distinct jsonb_build_object(
                            'id', cc.id,
                            'course_id', cc.course_id,
                            'title', cc.title,
                            'body', cc.body,
                            'order', cc."order",
                            'created_at', cc.created_at,
                            'updated_at', cc.updated_at
                        )) filter (where cc.id is not null), '[]'
                    ) as course_content,
                    to_jsonb(u) - 'password' as teacher
                from courses c
                join enrollments e
                  on e.course_id = c.id
                 and e.student_id = ?
                 and e.status = 'enrolled'
                 and e.deleted_at is null
                left join course_contents cc on cc.course_id = c.id
                left join users u on u.id = c.teacher_id
                group by c.id, u.id
                order by c.id asc
            SQL, [$user->id]);

            foreach ($rows as $row) {
                $coursesData[] = [
                    'id' => $row->id,
                    'title' => $row->title,
                    'short_description' => $row->short_description,
                    'description' => $row->description,
                    'teacher_id' => $row->teacher_id,
                    'category' => $row->category,
                    'price' => $row->price,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                    'course_content' => json_decode($row->course_content, true) ?? [],
                    'teacher' => json_decode($row->teacher, true),
                ];
            }
        } catch (\Throwable $e) {
            // leave coursesData empty on error
        }

        return response()->json([
            'message' => 'User retrieved successfully',
            'user' => $user->load('student', 'teacher'),
            'enrolledCourses' => $coursesData
        ], 200);
    }
}
