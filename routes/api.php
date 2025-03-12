<?php

use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ClassController;
use App\Http\Controllers\API\QrCodeController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\ScheduleController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\SubjectController;
use App\Http\Controllers\API\TeacherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Student routes
    Route::get('/schedule/today', [ScheduleController::class, 'todaySchedule']);
    Route::post('/attendance/scan', [AttendanceController::class, 'scanQrCode']);
    Route::get('/subjects', [AttendanceController::class, 'studentSubjects']);
    Route::get('/attendance/subject/{subjectId}', [AttendanceController::class, 'studentAttendanceBySubject']);
    
    // Admin/Teacher routes
    Route::middleware('can:admin-or-teacher')->group(function () {
        // QR Code generation
        Route::post('/qrcode/generate', [QrCodeController::class, 'generateQrCode']);
        Route::put('/qrcode/{qrCode}/deactivate', [QrCodeController::class, 'deactivateQrCode']);
        
        // Reports
        Route::get('/report/class', [ReportController::class, 'generateClassAttendanceReport']);
        Route::get('/report/student', [ReportController::class, 'generateStudentAttendanceReport']);
        
        // CRUD operations
        Route::apiResource('students', StudentController::class);
        Route::apiResource('classes', ClassController::class);
        Route::apiResource('subjects', SubjectController::class);
        Route::apiResource('schedules', ScheduleController::class);
        Route::apiResource('attendances', AttendanceController::class);
        Route::apiResource('teachers', TeacherController::class);
        
        // Additional student management routes
        Route::post('/students/{student}/assign-class', [StudentController::class, 'assignClass']);
        Route::post('/students/promote', [StudentController::class, 'promoteClass']);
        
        // Class students
        Route::get('/classes/{class}/students', [ClassController::class, 'students']);
    });
});

