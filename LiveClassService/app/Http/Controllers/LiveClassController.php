<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LiveClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

        $courseServiceBase = config('services.courses.url');
        $coursesById = [];
        if ($courseServiceBase) {
            $courseIds = $liveClass->pluck('course_id')->unique()->values()->all();
            if (!empty($courseIds)) {
                $res = Http::retry(2, 200)->timeout(5)->post(rtrim($courseServiceBase, '/').'/api/courses', [
                    'ids' => $courseIds
                ]);
                if ($res->successful()) {
                    $courses = $res->json('data') ?? [];
                    $coursesById = collect($courses)->keyBy('id')->all();
                }
            }
        }

        $transformed = $liveClass->map(function ($cls) use ($coursesById) {
            $arr = $cls->toArray();
            $arr['course'] = $coursesById[$cls->course_id] ?? null;
            return $arr;
        });

        return response()->json([
            'message' => 'Classes retrieved successfully',
            'data' => $transformed
        ], 200);
    }

    public function fetchAllClasses()
    {
        $liveClass = LiveClass::all();

        $courseServiceBase = config('services.courses.url');
        $coursesById = [];
        if ($courseServiceBase) {
            $courseIds = $liveClass->pluck('course_id')->unique()->values()->all();
            if (!empty($courseIds)) {
                $res = Http::retry(2, 200)->timeout(5)->post(rtrim($courseServiceBase, '/').'/api/courses', [
                    'ids' => $courseIds
                ]);
                if ($res->successful()) {
                    $courses = $res->json('data') ?? [];
                    $coursesById = collect($courses)->keyBy('id')->all();
                }
            }
        }

        $transformed = $liveClass->map(function ($cls) use ($coursesById) {
            $arr = $cls->toArray();
            $arr['course'] = $coursesById[$cls->course_id] ?? null;
            return $arr;
        });

        return response()->json([
            'message' => 'All Scheduled Classes retrieved',
            'data' => $transformed
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

    public function getByIds(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return response()->json([
                'message' => 'No class IDs provided',
                'data' => []
            ], 200);
        }

        $classes = LiveClass::whereIn('id', $ids)->get();

        return response()->json([
            'message' => 'Live classes fetched successfully',
            'data' => $classes
        ], 200);
    }
}
