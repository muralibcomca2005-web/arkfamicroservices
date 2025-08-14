<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $attendanceList = Attendance::with('user', 'teacher', 'liveClass.course')->where('live_class_id', $liveClassId)->get();

        if ($attendanceList->isEmpty()) {
            return response()->json([
                'message' => 'No attendance found for this class.',
            ], 404);
        }

        return response()->json([
            'message' => 'Attendance List retrieved successfully',
            'data' => $attendanceList
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
