<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $enrolledCourses = $user->enrolledCourses()->with('courseContent', 'teacher.user')->get();

        return response()->json([
            'message' => 'User retrieved successfully',
            'user' => $user->load('student', 'teacher'),
            'enrolledCourses' => $enrolledCourses
        ], 200);
    }
}
