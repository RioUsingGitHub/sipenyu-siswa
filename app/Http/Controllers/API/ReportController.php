<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function generateClassAttendanceReport(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $class = ClassRoom::findOrFail($request->class_id);
        $subject = Subject::findOrFail($request->subject_id);
        
        // Get the schedule for this class and subject
        $schedule = Schedule::where('class_id', $class->id)
            ->where('subject_id', $subject->id)
            ->where('is_active', true)
            ->first();
            
        if (!$schedule) {
            return response()->json([
                'message' => 'No active schedule found for this class and subject',
            ], 404);
        }

        // Get all students in the class
        $students = $class->activeStudents()->get();
        
        // Get date range
        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);
        $dateRange = [];
        
        for ($date = $dateFrom; $date->lte($dateTo); $date->addDay()) {
            $dateRange[] = $date->format('Y-m-d');
        }
        
        // Get attendance data
        $attendanceData = [];
        
        foreach ($students as $student) {
            $studentAttendance = [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'nisn' => $student->nisn,
                'attendance' => [],
            ];
            
            foreach ($dateRange as $date) {
                $attendance = Attendance::where('student_id', $student->id)
                    ->where('schedule_id', $schedule->id)
                    ->where('date', $date)
                    ->first();
                    
                $studentAttendance['attendance'][$date] = $attendance ? $attendance->status : null;
            }
            
            // Calculate summary
            $summary = [
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpa' => 0,
                'total' => count($dateRange),
            ];
            
            foreach ($studentAttendance['attendance'] as $status) {
                if ($status) {
                    $summary[$status]++;
                }
            }
            
            $studentAttendance['summary'] = $summary;
            $attendanceData[] = $studentAttendance;
        }
        
        // Generate PDF
        $data = [
            'class' => $class,
            'subject' => $subject,
            'schedule' => $schedule,
            'date_from' => $dateFrom->format('d F Y'),
            'date_to' => $dateTo->format('d F Y'),
            'date_range' => $dateRange,
            'attendance_data' => $attendanceData,
            'generated_at' => Carbon::now()->format('d F Y H:i'),
        ];
        
        $pdf = PDF::loadView('reports.class_attendance', $data);
        
        return $pdf->download("attendance_report_{$class->name}_{$subject->name}.pdf");
    }

    public function generateStudentAttendanceReport(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $student = Student::findOrFail($request->student_id);
        $activeClass = $student->activeClass();
        
        if (!$activeClass) {
            return response()->json([
                'message' => 'Student is not assigned to any active class',
            ], 404);
        }
        
        // Get date range
        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);
        
        // Query builder for attendances
        $query = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
            ->with(['schedule.subject', 'schedule.teacher']);
            
        // Filter by subject if provided
        if ($request->has('subject_id')) {
            $subject = Subject::findOrFail($request->subject_id);
            
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
            
            $subjectName = $subject->name;
        } else {
            $subjectName = 'All Subjects';
        }
        
        // Get attendance records
        $attendances = $query->orderBy('date')->get();
        
        // Group by subject
        $attendanceBySubject = [];
        foreach ($attendances as $attendance) {
            $subjectId = $attendance->schedule->subject_id;
            
            if (!isset($attendanceBySubject[$subjectId])) {
                $attendanceBySubject[$subjectId] = [
                    'subject_name' => $attendance->schedule->subject->name,
                    'teacher_name' => $attendance->schedule->teacher->name,
                    'records' => [],
                    'summary' => [
                        'hadir' => 0,
                        'izin' => 0,
                        'sakit' => 0,
                        'alpa' => 0,
                        'total' => 0,
                    ],
                ];
            }
            
            $attendanceBySubject[$subjectId]['records'][] = [
                'date' => Carbon::parse($attendance->date)->format('d F Y'),
                'meeting_number' => $attendance->meeting_number,
                'status' => $attendance->status,
            ];
            
            $attendanceBySubject[$subjectId]['summary'][$attendance->status]++;
            $attendanceBySubject[$subjectId]['summary']['total']++;
        }
        
        // Generate PDF
        $data = [
            'student' => $student,
            'class' => $activeClass,
            'subject_name' => $subjectName,
            'date_from' => $dateFrom->format('d F Y'),
            'date_to' => $dateTo->format('d F Y'),
            'attendance_by_subject' => $attendanceBySubject,
            'generated_at' => Carbon::now()->format('d F Y H:i'),
        ];
        
        $pdf = PDF::loadView('reports.student_attendance', $data);
        
        return $pdf->download("attendance_report_{$student->name}.pdf");
    }
}

