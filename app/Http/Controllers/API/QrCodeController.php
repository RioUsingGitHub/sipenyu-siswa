<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateQrCode;
use App\Models\QrCode;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QrCodeController extends Controller
{
    public function generateQrCode(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'meeting_number' => 'required|integer|min:1',
        ]);

        // Check if the user is authorized to generate QR codes
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isTeacher()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Get the schedule
        $schedule = Schedule::findOrFail($request->schedule_id);

        // If user is a teacher, check if they are assigned to this schedule
        if ($user->isTeacher() && $user->teacher && $schedule->teacher_id !== $user->teacher->id) {
            return response()->json([
                'message' => 'You are not authorized to generate QR codes for this schedule',
            ], 403);
        }

        // Dispatch the job to generate QR codes
        GenerateQrCode::dispatch($schedule->id, $request->meeting_number);

        return response()->json([
            'message' => 'QR code generation started successfully',
            'schedule_id' => $schedule->id,
            'meeting_number' => $request->meeting_number,
        ]);
    }

    public function deactivateQrCode(Request $request, QrCode $qrCode)
    {
        // Check if the user is authorized to deactivate QR codes
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isTeacher()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // If user is a teacher, check if they are assigned to this schedule
        if ($user->isTeacher() && $user->teacher && $qrCode->schedule->teacher_id !== $user->teacher->id) {
            return response()->json([
                'message' => 'You are not authorized to deactivate QR codes for this schedule',
            ], 403);
        }

        $qrCode->update(['is_active' => false]);

        return response()->json([
            'message' => 'QR code deactivated successfully',
        ]);
    }
}

