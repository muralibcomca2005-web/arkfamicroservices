<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function autoAttendance($studentID, $liveClassId)
    {
        if ($this->attendanceService->attendanceExists($studentID, $liveClassId)) {
            return response()->json([
                'message' => 'Attendance already marked'
            ], 200);
        }

        $attendance = $this->attendanceService->markAutoAttendance($studentID, $liveClassId);

        return response()->json([
            'message' => 'Attendance marked Successfully',
            'data' => $attendance
        ], 201);
    }

    public function getAttendanceList($liveClassId)
    {
        $attendanceList = Attendance::where('live_class_id', $liveClassId)->get();

        if ($attendanceList->isEmpty()) {
            return response()->json([
                'message' => 'No attendance found for this class.',
            ], 404);
        }

        $userServiceBase = config('services.users.url');
        $liveClassServiceBase = config('services.live_classes.url');
        $courseServiceBase = config('services.courses.url');

        $students = [];
        $teacher = null;
        $liveClass = null;
        $course = null;

        $studentIds = $attendanceList->pluck('student_id')->unique()->values()->all();
        if ($userServiceBase && !empty($studentIds)) {
            $userRes = Http::timeout(5)->post(rtrim($userServiceBase, '/').'/api/users/by-ids', [
                'ids' => $studentIds
            ]);
            if ($userRes->successful()) {
                $students = $userRes->json('data') ?? [];
            }
        }

        $teacherId = $attendanceList->first()->verified_by ?? null;
        if ($teacherId && $userServiceBase) {
            $teacherRes = Http::timeout(5)->post(rtrim($userServiceBase, '/').'/api/users/by-ids', [
                'ids' => [$teacherId]
            ]);
            if ($teacherRes->successful()) {
                $teacher = collect($teacherRes->json('data') ?? [])->first();
            }
        }

        if ($liveClassServiceBase) {
            $classRes = Http::timeout(5)->post(rtrim($liveClassServiceBase, '/').'/api/live-classes/by-ids', [
                'ids' => [$liveClassId]
            ]);
            if ($classRes->successful()) {
                $liveClass = collect($classRes->json('data') ?? [])->first();
                if ($liveClass && $courseServiceBase) {
                    $courseRes = Http::timeout(5)->post(rtrim($courseServiceBase, '/').'/api/courses', [
                        'ids' => [$liveClass['course_id']]
                    ]);
                    if ($courseRes->successful()) {
                        $course = collect($courseRes->json('data') ?? [])->first();
                    }
                }
            }
        }

        $data = [
            'attendance' => $attendanceList,
            'students' => $students,
            'teacher' => $teacher,
            'live_class' => $liveClass,
            'course' => $course,
        ];

        return response()->json([
            'message' => 'Attendance List retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function reviewAttendance(Request $request, $attendanceId, $teacherId)
    {
        $data = $request->validate([
            'status' => 'required|array',
            'status.*.id' => 'required|integer|exists:attendances,id',
            'status.*.status' => ['required', Rule::in(['present', 'absent'])],
        ]);

        $updated = $this->attendanceService->bulkUpdateAttendance($data['status'], $teacherId);

        return response()->json([
            'message' => 'Attendance Verified Successfully',
            'data' => $updated
        ], 200);
    }

    public function totalClassesAttended($id)
    {
        $totalClassesAttended = $this->attendanceService->getTotalClassesAttended($id);

        return response()->json([
            'message' => 'Total Classes attended by a student in all courses',
            'data' => $totalClassesAttended
        ], 200);
    }

    public function totalHrs($id)
    {
        $timeData = $this->attendanceService->calculateTotalTime($id);

        return response()->json([
            'message' => 'Total Time spent by a student',
            'data' => [
                'hours' => $timeData['hours'],
                'minutes' => $timeData['minutes']
            ]
        ], 200);
    }

    public function getAttendancesByUser(string $userId)
    {
        // This assumes the 'user_id' is a foreign key on the 'attendances' table.
        $attendances = Attendance::where('user_id', $userId)->get();

        return response()->json([
            'message' => 'Attendance records fetched successfully',
            'data' => $attendances
        ], 200);
    }
}
