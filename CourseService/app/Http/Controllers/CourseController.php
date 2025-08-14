<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use App\Models\CourseContent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;

class CourseController extends Controller
{
    public function addCourse(StoreCourseRequest $request)
    {
        if (!Gate::allows('only-admin')) {
            return response()->json([
                'message' => 'Unauthorized User',
            ], 403);
        }
        $data = $request->validated();

        $course = Course::create([
            'title' => $data['title'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'category' => $data['category'],
            'price' => $data['price'],
        ]);

        foreach ($data['lessons'] as $lesson) {
            CourseContent::create([
                'course_id' => $course->id,
                'title' => $lesson['title'],
                'body' => $lesson['body'],
                'order' => $lesson['order']
            ]);
        }

        return response()->json([
            'message' => 'Course created successfully',
            'data' => $course->load('courseContent'),
        ], 201);
    }

    public function fetchCourse()
    {
        $courses = Course::with('courseContent')->orderBy('id', 'asc')->get();

        if ($courses->isEmpty()) {
            return response()->json([
                'message' => 'No Courses',
            ], 404);
        }

        return response()->json([
            'message' => 'Courses retrieved Successfully',
            'data' => $this->attachTeacherData($courses)
        ]);
    }

    public function assignTeachers(Request $request, $id)
    {
        if (!Gate::allows('only-admin')) {
            return response()->json([
                'message' => 'Unauthorized User',
            ], 403);
        }
        $course = Course::findOrFail($id);

        $data = $request->validate([
            'teacher_id' => 'required|integer|min:1'
        ]);

        // Optionally validate teacher via User Service
        $course->teacher_id = $data['teacher_id'];
        $course->save();

        return response()->json([
            'message' => 'Teacher assigned to course successfully',
            'course' => $course
        ], 200);
    }

    public function removeAssignedTeacher($id)
    {
        if (!Gate::allows('only-admin')) {
            return response()->json([
                'message' => 'Unauthorized User',
            ], 403);
        }
        $course = Course::findOrFail($id);
        $course->update([
            'teacher_id' => null
        ]);
        $course->save();

        return response()->json([
            'message' => 'Teacher removed from the assigned course successfully',
            'course' => $course
        ], 200);
    }

    public function getCourseByIds(Request $request)
    {
        $courseIds = $request->input('ids', []);

        if (empty($courseIds)) {
            return response()->json([
                'message' => 'No course IDs provided.',
                'data' => []
            ], 200);
        }

        // $courseIds = explode(',', $courseIdsString);
        $courses = Course::whereIn('id', $courseIds)->with('courseContent')->get();

        return response()->json([
            'message' => 'Courses fetched successfully',
            'data' => $this->attachTeacherData($courses)
        ], 200,);
    }

    public function getCoursesByTeacher($tchId)
    {
        $courses = Course::where('teacher_id', $tchId)->with('courseContent')->get();
        return response()->json([
            'message' => 'Courses fetched successfully',
            'data' => $this->attachTeacherData($courses)
        ], 200);
    }

    private function attachTeacherData($courses)
    {
        $userServiceBase = config('services.users.url');
        if (!$userServiceBase) {
            return $courses;
        }

        $teacherIds = collect($courses)->pluck('teacher_id')->filter()->unique()->values()->all();
        if (empty($teacherIds)) {
            return $courses;
        }

        $res = Http::timeout(5)->post(rtrim($userServiceBase, '/').'/api/users/by-ids', [
            'ids' => $teacherIds
        ]);
        $teachersById = [];
        if ($res->successful()) {
            $teachersById = collect($res->json('data') ?? [])->keyBy('id');
        }

        return collect($courses)->map(function ($course) use ($teachersById) {
            $arr = $course->toArray();
            $arr['teacher'] = $arr['teacher_id'] ? ($teachersById[$arr['teacher_id']] ?? null) : null;
            return $arr;
        });
    }
}
