<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with(['class', 'subject', 'teacher']);

        // Filter by class
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by subject
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by teacher
        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by day
        if ($request->has('day')) {
            $query->where('day', $request->day);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $schedules = $query->orderBy('day')->orderBy('time_start')->get();

        return response()->json([
            'schedules' => $schedules,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'required|string',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'year' => 'required|string|max:10',
            'semester' => 'required|in:1,2',
            'is_active' => 'boolean',
        ]);

        $schedule = Schedule::create([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
            'day' => $request->day,
            'time_start' => $request->time_start,
            'time_end' => $request->time_end,
            'year' => $request->year,
            'semester' => $request->semester,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'message' => 'Schedule created successfully',
            'schedule' => $schedule->load(['class', 'subject', 'teacher']),
        ], 201);
    }

    public function show(Schedule $schedule)
    {
        $schedule->load(['class', 'subject', 'teacher']);

        return response()->json([
            'schedule' => $schedule,
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'required|string',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'year' => 'required|string|max:10',
            'semester' => 'required|in:1,2',
            'is_active' => 'boolean',
        ]);

        $schedule->update([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
            'day' => $request->day,
            'time_start' => $request->time_start,
            'time_end' => $request->time_end,
            'year' => $request->year,
            'semester' => $request->semester,
            'is_active' => $request->boolean('is_active', $schedule->is_active),
        ]);

        return response()->json([
            'message' => 'Schedule updated successfully',
            'schedule' => $schedule->load(['class', 'subject', 'teacher']),
        ]);
    }

    public function destroy(Schedule $schedule)
    {
        // Check if schedule has attendances
        if ($schedule->attendances()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete schedule with attendance records',
            ], 422);
        }

        $schedule->delete();

        return response()->json([
            'message' => 'Schedule deleted successfully',
        ]);
    }

    public function todaySchedule(Request $request)
    {
        $user = $request->user();
        $today = Carbon::now()->locale('id')->isoFormat('dddd');

        if ($user instanceof Student) {
            $activeClass = $user->activeClass();
            
            if (!$activeClass) {
                return response()->json([
                    'message' => 'Student is not assigned to any active class',
                    'schedule' => [],
                ], 404);
            }

            $schedules = Schedule::with(['subject', 'teacher'])
                ->where('class_id', $activeClass->id)
                ->where('day', $today)
                ->where('is_active', true)
                ->orderBy('time_start')
                ->get()
                ->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'name' => $schedule->subject->name,
                        'teacher_name' => $schedule->teacher->name,
                        'day' => $schedule->day,
                        'time_start' => $schedule->time_start,
                        'time_end' => $schedule->time_end,
                    ];
                });

            return response()->json([
                'schedule' => $schedules,
            ]);
        }

        return response()->json([
            'message' => 'Unauthorized',
        ], 403);
    }
}

