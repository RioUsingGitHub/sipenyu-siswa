<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\QrCode;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['student', 'schedule.subject', 'schedule.class']);

        // Filter by student
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by schedule
        if ($request->has('schedule_id')) {
            $query->where('schedule_id', $request->schedule_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        } else if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        } else if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        } else if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(15);

        return response()->json([
            'attendances' => $attendances,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date',
            'meeting_number' => 'required|integer|min:1',
            'status' => 'required|in:hadir,izin,sakit,alpa',
            'notes' => 'nullable|string',
        ]);

        // Check if attendance already exists
        $existingAttendance = Attendance::where('student_id', $request->student_id)
            ->where('schedule_id', $request->schedule_id)
            ->where('date', $request->date)
            ->first();

        if ($existingAttendance) {
            $existingAttendance->update([
                'meeting_number' => $request->meeting_number,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'message' => 'Attendance updated successfully',
                'attendance' => $existingAttendance,
            ]);
        }

        $attendance = Attendance::create([
            'student_id' => $request->student_id,
            'schedule_id' => $request->schedule_id,
            'date' => $request->date,
            'meeting_number' => $request->meeting_number,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Attendance created successfully',
            'attendance' => $attendance,
        ], 201);
    }

    public function show(Attendance $attendance)
    {
        $attendance->load(['student', 'schedule.subject', 'schedule.class', 'schedule.teacher']);

        return response()->json([
            'attendance' => $attendance,
        ]);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status' => 'required|in:hadir,izin,sakit,alpa',
            'notes' => 'nullable|string',
        ]);

        $attendance->update([
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Attendance updated successfully',
            'attendance' => $attendance,
        ]);
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return response()->json([
            'message' => 'Attendance deleted successfully',
        ]);
    }

    public function scanQrCode(Request $request)
    {
        $request->validate([
            'qrToken' => 'required|string',
        ]);

        $user = $request->user();

        if (!($user instanceof Student)) {
            return response()->json([
                'message' => 'Only students can scan QR codes',
            ], 403);
        }

        // Find the QR code
        $qrCode = QrCode::where('token', $request->qrToken)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();

        if (!$qrCode) {
            return response()->json([
                'message' => 'QR Code tidak valid atau sudah kadaluarsa',
            ], 422);
        }

        // Get the schedule
        $schedule = $qrCode->schedule;

        // Check if student is in the class
        $activeClass = $user->activeClass();
        if (!$activeClass || $activeClass->id !== $schedule->class_id) {
            return response()->json([
                'message' => 'Anda tidak terdaftar di kelas ini',
            ], 422);
        }

        // Check if attendance already exists
        $existingAttendance = Attendance::where('student_id', $user->id)
            ->where('schedule_id', $schedule->id)
            ->where('date', now()->toDateString())
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'Anda sudah melakukan presensi untuk kelas ini hari ini',
                'attendance' => [
                    'student_name' => $user->name,
                    'class_name' => $activeClass->name,
                    'subject_name' => $schedule->subject->name,
                    'time' => $schedule->time_start . ' - ' . $schedule->time_end,
                    'teacher_name' => $schedule->teacher->name,
                ],
            ]);
        }

        // Create attendance record
        $attendance = Attendance::create([
            'student_id' => $user->id,
            'schedule_id' => $schedule->id,
            'date' => now()->toDateString(),
            'meeting_number' => $qrCode->meeting_number,
            'status' => 'hadir',
        ]);

        return response()->json([
            'message' => 'Presensi berhasil',
            'attendance' => [
                'student_name' => $user->name,
                'class_name' => $activeClass->name,
                'subject_name' => $schedule->subject->name,
                'time' => $schedule->time_start . ' - ' . $schedule->time_end,
                'teacher_name' => $schedule->teacher->name,
            ],
        ]);
    }

    public function studentSubjects(Request $request)
    {
        $user = $request->user();

        if (!($user instanceof Student)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $activeClass = $user->activeClass();
        
        if (!$activeClass) {
            return response()->json([
                'message' => 'Student is not assigned to any active class',
                'subjects' => [],
            ], 404);
        }

        // Get all subjects for the student's class
        $subjects = Subject::whereHas('schedules', function ($query) use ($activeClass) {
            $query->where('class_id', $activeClass->id)
                  ->where('is_active', true);
        })->get();

        return response()->json([
            'subjects' => $subjects,
        ]);
    }

    public function studentAttendanceBySubject(Request $request, $subjectId)
    {
        $user = $request->user();

        if (!($user instanceof Student)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $subject = Subject::findOrFail($subjectId);
        $activeClass = $user->activeClass();
        
        if (!$activeClass) {
            return response()->json([
                'message' => 'Student is not assigned to any active class',
                'attendances' => [],
            ], 404);
        }

        // Get schedules for this subject and class
        $schedules = Schedule::where('subject_id', $subjectId)
            ->where('class_id', $activeClass->id)
            ->where('is_active', true)
            ->pluck('id');

        if ($schedules->isEmpty()) {
            return response()->json([
                'message' => 'No schedules found for this subject',
                'subject_name' => $subject->name,
                'attendances' => [],
            ]);
        }

        // Get attendance records
        $attendances = Attendance::where('student_id', $user->id)
            ->whereIn('schedule_id', $schedules)
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($attendance) {
                $schedule = $attendance->schedule;
                return [
                    'meeting_number' => $attendance->meeting_number,
                    'date' => Carbon::parse($attendance->date)->format('d F Y'),
                    'time_start' => $schedule->time_start,
                    'time_end' => $schedule->time_end,
                    'status' => $attendance->status,
                    'status_text' => ucfirst($attendance->status),
                ];
            });

        return response()->json([
            'subject_name' => $subject->name,
            'attendances' => $attendances,
        ]);
    }
}

