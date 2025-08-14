<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{

    public function addStudent(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',

            'department' => 'nullable|string|max:255',
            'selected_course' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'address' => 'nullable|string',
            'phone' => ['nullable', 'regex:/^\+?[1-9]\d{7,14}$/'],
            'country' => 'required|string|max:100',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::STUDENT,
        ]);

        $student = Student::create([
            'reg_no' => Student::regNo(),
            'user_id' => $user->id,
            'phone' => $data['phone'],
            'country' => $data['country'],
        ]);

        return response()->json([
            'message' => 'User with Student role created',
            'data' => ['user' => $user, 'student' => $student]
        ]);
    }
}
