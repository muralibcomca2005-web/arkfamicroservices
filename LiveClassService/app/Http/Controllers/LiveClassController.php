<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LiveClass;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LiveClassController extends Controller
{
    public function addClass(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer',
            'topic' => 'required|string',
            'meeting_link' => 'required|string',
            'scheduled_at' => 'required|date',
        ]);

        $liveClass = LiveClass::create([
            'course_id' => $validated['course_id'],
            'topic' => $validated['topic'],
            'meeting_link' => $validated['meeting_link'],
            'scheduled_at' => $validated['scheduled_at'],
        ]);

        return response()->json([
            'message' => 'Meeting Schedule Updated',
            'data' => $liveClass
        ], 201);
    }

    public function fetchClasses(Request $request)
    {
        $id = $request->input('course_ids');
        $liveClass = LiveClass::whereIn('course_id', $id)->get();

        if ($liveClass->isEmpty()) {
            return response()->json([
                'message' => 'No classes scheduled till now'
            ], 200);
        }

        return response()->json([
            'message' => 'Classes retrieved successfully',
            'data' => $liveClass->load('course.teacher.user')
        ], 200);
    }

    public function fetchAllClasses()
    {
        $liveClass = LiveClass::all();

        return response()->json([
            'message' => 'All Scheduled Classes retrieved',
            'data' => $liveClass->load('course.teacher.user')
        ], 200);
    }

    public function endClass($id)
    {
        $liveClass = LiveClass::find($id);

        if (!$liveClass) {
            return response()->json([
                'message' => 'No Classes Found'
            ], 404);
        }

        $liveClass->update([
            'class_ended' => true
        ]);

        Attendance::where('live_class_id', $liveClass->id)->whereNull('leave_time')->update([
            'leave_time' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Class ended successfully',
            'data' => $liveClass
        ], 200);
    }
}
