<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassRoom::query();

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by grade
        if ($request->has('grade')) {
            $query->where('grade', $request->grade);
        }

        // Filter by year
        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $classes = $query->orderBy('grade')->orderBy('name')->get();

        return response()->json([
            'classes' => $classes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'required|integer|min:7|max:9',
            'year' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);

        $class = ClassRoom::create([
            'name' => $request->name,
            'grade' => $request->grade,
            'year' => $request->year,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'message' => 'Class created successfully',
            'class' => $class,
        ], 201);
    }

    public function show(ClassRoom $class)
    {
        $class->load('activeStudents');

        return response()->json([
            'class' => $class,
            'students_count' => $class->activeStudents->count(),
            'students' => $class->activeStudents,
        ]);
    }

    public function update(Request $request, ClassRoom $class)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'required|integer|min:7|max:9',
            'year' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);

        $class->update([
            'name' => $request->name,
            'grade' => $request->grade,
            'year' => $request->year,
            'is_active' => $request->boolean('is_active', $class->is_active),
        ]);

        return response()->json([
            'message' => 'Class updated successfully',
            'class' => $class,
        ]);
    }

    public function destroy(ClassRoom $class)
    {
        // Check if class has active students
        if ($class->activeStudents()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete class with active students',
            ], 422);
        }

        $class->delete();

        return response()->json([
            'message' => 'Class deleted successfully',
        ]);
    }

    public function students(ClassRoom $class)
    {
        $students = $class->activeStudents()->get();

        return response()->json([
            'class' => $class->name,
            'students_count' => $students->count(),
            'students' => $students,
        ]);
    }
}

