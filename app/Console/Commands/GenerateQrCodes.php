<?php

namespace App\Console\Commands;

use App\Models\QrCode;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateQrCodes extends Command
{
    protected $signature = 'qrcode:generate {schedule_id?} {--all}';
    protected $description = 'Generate QR codes for attendance';

    public function handle()
    {
        $scheduleId = $this->argument('schedule_id');
        $all = $this->option('all');

        if (!$scheduleId && !$all) {
            $this->error('Please provide a schedule ID or use --all option');
            return 1;
        }

        if ($scheduleId) {
            $schedules = Schedule::where('id', $scheduleId)
                ->where('is_active', true)
                ->get();
        } else {
            // Get current day of week
            $today = Carbon::now()->locale('id')->isoFormat('dddd');
            
            // Get schedules for today
            $schedules = Schedule::where('day', $today)
                ->where('is_active', true)
                ->get();
        }

        if ($schedules->isEmpty()) {
            $this->info('No active schedules found');
            return 0;
        }

        $count = 0;
        foreach ($schedules as $schedule) {
            // Deactivate existing QR codes
            QrCode::where('schedule_id', $schedule->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
            
            // Get the latest meeting number
            $latestAttendance = $schedule->attendances()
                ->orderBy('meeting_number', 'desc')
                ->first();
            
            $meetingNumber = $latestAttendance ? $latestAttendance->meeting_number : 1;
            
            // Generate new QR code
            $token = Str::random(32);
            $expiresAt = Carbon::now()->addSeconds(6);
            
            QrCode::create([
                'schedule_id' => $schedule->id,
                'token' => $token,
                'meeting_number' => $meetingNumber,
                'expires_at' => $expiresAt,
                'is_active' => true,
            ]);
            
            $count++;
        }

        $this->info("Generated {$count} QR codes successfully");
        return 0;
    }
}

