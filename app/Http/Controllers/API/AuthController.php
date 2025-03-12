<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // First try to authenticate as a user (admin/teacher)
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token,
            ]);
        }

        // If not a user, try to authenticate as a student
        $student = Student::where('nisn', $request->email)->first();

        if ($student && Hash::check($request->password, $student->password)) {
            $token = $student->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'class' => $student->activeClass() ? $student->activeClass()->name : null,
                    'role' => 'student',
                ],
                'token' => $token,
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['Username atau sandi salah. Coba lagi!'],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        $user = $request->user();

        if ($user instanceof User) {
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ]);
        } else {
            // Student
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nisn' => $user->nisn,
                    'class' => $user->activeClass() ? $user->activeClass()->name : null,
                    'role' => 'student',
                ],
            ]);
        }
    }
}

