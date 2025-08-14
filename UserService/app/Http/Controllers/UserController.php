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

    public function getUsersByIds(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return response()->json([
                'message' => 'No user IDs provided.',
                'data' => []
            ], 200);
        }

        $users = User::whereIn('id', $ids)->with(['teacher', 'student'])->get();

        return response()->json([
            'message' => 'Users fetched successfully',
            'data' => $users
        ], 200);
    }
}
