<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::with('user');

        // Search by name or NIP
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $teachers = $query->paginate(15);

        return response()->json([
            'teachers' => $teachers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'nullable|string|max:20|unique:teachers',
            'name' => 'required|string|max:255',
            'gender' => 'nullable|in:L,P',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('teachers', 'public');
        }

        // Create user account
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'teacher',
        ]);

        // Create teacher profile
        $teacher = Teacher::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'address' => $request->address,
            'photo' => $photoPath,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Teacher created successfully',
            'teacher' => $teacher->load('user'),
        ], 201);
    }

    public function show(Teacher $teacher)
    {
        $teacher->load('user');

        return response()->json([
            'teacher' => $teacher,
        ]);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'nip' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('teachers')->ignore($teacher->id),
            ],
            'name' => 'required|string|max:255',
            'gender' => 'nullable|in:L,P',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($teacher->user_id),
            ],
            'password' => 'nullable|string|min:6',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($teacher->photo) {
                Storage::disk('public')->delete($teacher->photo);
            }
            $photoPath = $request->file('photo')->store('teachers', 'public');
            $teacher->photo = $photoPath;
        }

        // Update teacher profile
        $teacher->nip = $request->nip;
        $teacher->name = $request->name;
        $teacher->gender = $request->gender;
        $teacher->phone = $request->phone;
        $teacher->address = $request->address;
        $teacher->save();

        // Update user account
        if ($teacher->user) {
            $teacher->user->name = $request->name;
            $teacher->user->email = $request->email;
            if ($request->filled('password')) {
                $teacher->user->password = Hash::make($request->password);
            }
            $teacher->user->save();
        }

        return response()->json([
            'message' => 'Teacher updated successfully',
            'teacher' => $teacher->load('user'),
        ]);
    }

    public function destroy(Teacher $teacher)
    {
        // Delete photo if exists
        if ($teacher->photo) {
            Storage::disk('public')->delete($teacher->photo);
        }

        // Delete user account if exists
        if ($teacher->user) {
            $teacher->user->delete();
        }

        $teacher->delete();

        return response()->json([
            'message' => 'Teacher deleted successfully',
        ]);
    }
}

