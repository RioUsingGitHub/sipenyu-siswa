<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();

        // Filter by class if provided
        if ($request->has('class_id')) {
            $query->whereHas('classes', function ($q) use ($request) {
                $q->where('class_id', $request->class_id)
                  ->where('is_active', true);
            });
        }

        // Search by name or NISN
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(15);

        return response()->json([
            'students' => $students,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string|max:20|unique:students',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'gender' => 'nullable|in:L,P',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'class_id' => 'required|exists:classes,id',
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('students', 'public');
        }

        $student = Student::create([
            'nisn' => $request->nisn,
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'phone' => $request->phone,
            'address' => $request->address,
            'photo' => $photoPath,
        ]);

        // Assign student to class
        $student->classes()->attach($request->class_id, ['is_active' => true]);

        return response()->json([
            'message' => 'Student created successfully',
            'student' => $student,
        ], 201);
    }

    public function show(Student $student)
    {
        $student->load(['classes' => function ($query) {
            $query->orderBy('year', 'desc');
        }]);

        $activeClass = $student->activeClass();

        return response()->json([
            'student' => $student,
            'active_class' => $activeClass,
            'class_history' => $student->classes,
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'nisn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students')->ignore($student->id),
            ],
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
            'gender' => 'nullable|in:L,P',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $photoPath = $request->file('photo')->store('students', 'public');
            $student->photo = $photoPath;
        }

        $student->nisn = $request->nisn;
        $student->name = $request->name;
        if ($request->filled('password')) {
            $student->password = Hash::make($request->password);
        }
        $student->gender = $request->gender;
        $student->phone = $request->phone;
        $student->address = $request->address;
        $student->save();

        return response()->json([
            'message' => 'Student updated successfully',
            'student' => $student,
        ]);
    }

    public function destroy(Student $student)
    {
        // Delete photo if exists
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully',
        ]);
    }

    public function assignClass(Request $request, Student $student)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        // Deactivate current active class if exists
        $student->classes()->updateExistingPivot(
            $student->activeClass()->id ?? 0,
            ['is_active' => false],
            false
        );

        // Check if student is already in this class
        if ($student->classes()->where('class_id', $request->class_id)->exists()) {
            // Just reactivate
            $student->classes()->updateExistingPivot(
                $request->class_id,
                ['is_active' => true]
            );
        } else {
            // Assign to new class
            $student->classes()->attach($request->class_id, ['is_active' => true]);
        }

        return response()->json([
            'message' => 'Student assigned to class successfully',
            'active_class' => $student->activeClass(),
        ]);
    }

    public function promoteClass(Request $request)
    {
        $request->validate([
            'from_class_id' => 'required|exists:classes,id',
            'to_class_id' => 'required|exists:classes,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $fromClass = ClassRoom::findOrFail($request->from_class_id);
        $toClass = ClassRoom::findOrFail($request->to_class_id);

        foreach ($request->student_ids as $studentId) {
            $student = Student::findOrFail($studentId);
            
            // Deactivate current class
            $student->classes()->updateExistingPivot(
                $fromClass->id,
                ['is_active' => false]
            );
            
            // Assign to new class
            $student->classes()->attach($toClass->id, ['is_active' => true]);
        }

        return response()->json([
            'message' => 'Students promoted successfully',
            'from_class' => $fromClass->name,
            'to_class' => $toClass->name,
            'promoted_count' => count($request->student_ids),
        ]);
    }
}

