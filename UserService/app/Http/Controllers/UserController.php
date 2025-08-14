<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function addAdmin(Request $request)
    {
        if (!Gate::allows('only-admin')) {
            return response()->json([
                'message' => 'Unauthorized User',
            ], 403);
        }
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
            'role' => ['required', Rule::enum(UserRole::class)]

        ]);

        $user = User::create([
            ...$data,
            'role' => UserRole::ADMIN,
        ]);

        return response()->json([
            'message' => 'User with Admin role created',
            'user' => $user
        ], 201);
    }

    public function fetchUsers()
    {

        if (!Gate::allows('only-admin')) {
            return response()->json([
                'message' => 'Unauthorized User',
            ], 403);
        }
        $users = User::with(['teacher', 'student'])->get();

        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'No user found',
                'users' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Users with Admin, Teacher and Student roles fetched',
            'users' => $users
        ], 200);
    }
}
