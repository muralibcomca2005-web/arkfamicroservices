<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\Enrollment;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;


class EnrollmentController extends Controller
{
    public function enrollRequest($courseId, $stu_id)
    {
        $courseServiceBase = config('services.courses.url');
        $courseExists = false;
        if ($courseServiceBase) {
            $res = Http::timeout(5)->post(rtrim($courseServiceBase, '/').'/api/courses', [
                'ids' => [$courseId]
            ]);
            if ($res->successful()) {
                $courseExists = !empty($res->json('data'));
            }
        }
        if (!$courseExists) {
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
        $enroll = Enrollment::where('student_id', $id)->where('status', 'pending')->get();
        if ($enroll->isEmpty()) {
            return response()->json([
                'message' => "There is no pending course requests"
            ], 404);
        }

        $courseIds = $enroll->pluck('course_id')->unique()->values()->all();
        $studentIds = $enroll->pluck('student_id')->unique()->values()->all();

        $courseServiceBase = config('services.courses.url');
        $userServiceBase = config('services.users.url');

        $coursesById = [];
        if ($courseServiceBase && !empty($courseIds)) {
            $res = Http::timeout(5)->post(rtrim($courseServiceBase, '/').'/api/courses', [
                'ids' => $courseIds
            ]);
            if ($res->successful()) {
                $coursesById = collect($res->json('data') ?? [])->keyBy('id')->all();
            }
        }

        $studentsById = [];
        if ($userServiceBase && !empty($studentIds)) {
            $userRes = Http::timeout(5)->post(rtrim($userServiceBase, '/').'/api/users/by-ids', [
                'ids' => $studentIds
            ]);
            if ($userRes->successful()) {
                $studentsById = collect($userRes->json('data') ?? [])->keyBy('id')->all();
            }
        }

        $data = $enroll->map(function ($enr) use ($coursesById, $studentsById) {
            $arr = $enr->toArray();
            $arr['course'] = $coursesById[$enr->course_id] ?? null;
            $arr['student'] = $studentsById[$enr->student_id] ?? null;
            return $arr;
        });

        return response()->json([
            'message' => 'Pending Courses',
            'data' => $data,
        ]);
    }

    public function fetchAllPendingRequest()
    {
        $enroll = Enrollment::where('status', 'pending')->get();
        if ($enroll->isEmpty()) {
            return response()->json([
                'message' => "There is no pending course requests"
            ], 404);
        }

        $courseIds = $enroll->pluck('course_id')->unique()->values()->all();
        $studentIds = $enroll->pluck('student_id')->unique()->values()->all();

        $courseServiceBase = config('services.courses.url');
        $userServiceBase = config('services.users.url');

        $coursesById = [];
        if ($courseServiceBase && !empty($courseIds)) {
            $res = Http::timeout(5)->post(rtrim($courseServiceBase, '/').'/api/courses', [
                'ids' => $courseIds
            ]);
            if ($res->successful()) {
                $coursesById = collect($res->json('data') ?? [])->keyBy('id')->all();
            }
        }

        $studentsById = [];
        if ($userServiceBase && !empty($studentIds)) {
            $userRes = Http::timeout(5)->post(rtrim($userServiceBase, '/').'/api/users/by-ids', [
                'ids' => $studentIds
            ]);
            if ($userRes->successful()) {
                $studentsById = collect($userRes->json('data') ?? [])->keyBy('id')->all();
            }
        }

        $data = $enroll->map(function ($enr) use ($coursesById, $studentsById) {
            $arr = $enr->toArray();
            $arr['course'] = $coursesById[$enr->course_id] ?? null;
            $arr['student'] = $studentsById[$enr->student_id] ?? null;
            return $arr;
        });

        return response()->json([
            'message' => 'Pending Courses',
            'data' => $data,
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

        $courseIds = $enroll->pluck('course_id')->unique()->values()->all();

        $courseServiceBase = config('services.courses.url');
        $courses = [];
        if ($courseServiceBase && !empty($courseIds)) {
            $res = Http::timeout(5)->post(rtrim($courseServiceBase, '/').'/api/courses', [
                'ids' => $courseIds
            ]);
            if ($res->successful()) {
                $courses = $res->json('data') ?? [];
            }
        }

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

        $courseIds = $enrollmentRecords->pluck('course_id')->unique()->values()->all();

        $courseServiceBase = config('services.courses.url');
        $courses = [];
        if ($courseServiceBase && !empty($courseIds)) {
            $res = Http::timeout(5)->post(rtrim($courseServiceBase, '/').'/api/courses', [
                'ids' => $courseIds
            ]);
            if ($res->successful()) {
                $courses = $res->json('data') ?? [];
            }
        }

        return response()->json(['data' => $courses], 200);
    }

    public function getEnrollmentsByCourseIds(Request $request)
    {
        $courseIds = $request->query('ids', []);
        if (!is_array($courseIds)) {
            $courseIds = [$courseIds];
        }
        $courseIds = array_filter(array_map('intval', $courseIds));

        if (empty($courseIds)) {
            return response()->json([
                'message' => 'No course IDs provided.',
                'data' => []
            ], 200);
        }

        $enrollments = Enrollment::whereIn('course_id', $courseIds)->get();

        $userServiceBase = config('services.users.url');
        $courseServiceBase = config('services.courses.url');

        $studentIds = $enrollments->pluck('student_id')->unique()->values()->all();
        $studentsById = [];
        if ($userServiceBase && !empty($studentIds)) {
            $userRes = Http::timeout(5)->post(rtrim($userServiceBase, '/').'/api/users/by-ids', [
                'ids' => $studentIds
            ]);
            if ($userRes->successful()) {
                $studentsById = collect($userRes->json('data') ?? [])->keyBy('id')->all();
            }
        }

        $coursesById = [];
        if ($courseServiceBase && !empty($courseIds)) {
            $courseRes = Http::timeout(5)->post(rtrim($courseServiceBase, '/').'/api/courses', [
                'ids' => $courseIds
            ]);
            if ($courseRes->successful()) {
                $coursesById = collect($courseRes->json('data') ?? [])->keyBy('id')->all();
            }
        }

        $data = $enrollments->map(function ($enr) use ($studentsById, $coursesById) {
            $arr = $enr->toArray();
            $arr['user'] = $studentsById[$enr->student_id] ?? null;
            $arr['course'] = $coursesById[$enr->course_id] ?? null;
            return $arr;
        });

        return response()->json([
            'message' => 'Enrollments fetched successfully',
            'data' => $data
        ], 200);
    }
}
