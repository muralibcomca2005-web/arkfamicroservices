<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Calculate total hours and minutes spent by a student
     */
    public function calculateTotalTime(int $studentId): array
    {
        $totalMinutes = Attendance::where('student_id', $studentId)
            ->whereNotNull('leave_time')
            ->get()
            ->sum(function (Attendance $attendance) {
                $join = Carbon::parse($attendance->join_time)->timezone('Asia/Kolkata');
                $leave = Carbon::parse($attendance->leave_time)->timezone('Asia/Kolkata');
                
                return $join->diffInMinutes($leave);
            });

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return [
            'hours' => $hours,
            'minutes' => $minutes,
            'total_minutes' => $totalMinutes
        ];
    }

    /**
     * Get total classes attended by a student
     */
    public function getTotalClassesAttended(int $studentId): int
    {
        return Attendance::where('student_id', $studentId)
            ->where('status', 'present')
            ->count();
    }

    /**
     * Check if attendance already exists for a student in a live class
     */
    public function attendanceExists(int $studentId, int $liveClassId): bool
    {
        return Attendance::where('student_id', $studentId)
            ->where('live_class_id', $liveClassId)
            ->exists();
    }

    /**
     * Mark automatic attendance for a student
     */
    public function markAutoAttendance(int $studentId, int $liveClassId): Attendance
    {
        return Attendance::create([
            'student_id' => $studentId,
            'live_class_id' => $liveClassId,
            'status' => 'present',
            'join_time' => Carbon::now()
        ]);
    }

    /**
     * Bulk update attendance status
     */
    public function bulkUpdateAttendance(array $attendanceData, int $teacherId): array
    {
        $updated = [];

        foreach ($attendanceData as $item) {
            $attendance = Attendance::find($item['id']);
            if ($attendance) {
                $attendance->update([
                    'status' => $item['status'],
                    'verified' => true,
                    'verified_by' => $teacherId,
                    'verified_time' => Carbon::now()
                ]);
                $updated[] = $attendance;
            }
        }

        return $updated;
    }
}