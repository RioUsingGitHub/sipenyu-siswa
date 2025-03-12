<?php

namespace App\Jobs;

use App\Events\QrCodeGenerated;
use App\Models\QrCode;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GenerateQrCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scheduleId;
    protected $meetingNumber;

    /**
     * Create a new job instance.
     */
    public function __construct($scheduleId, $meetingNumber)
    {
        $this->scheduleId = $scheduleId;
        $this->meetingNumber = $meetingNumber;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schedule = Schedule::find($this->scheduleId);
        
        if (!$schedule || !$schedule->is_active) {
            return;
        }

        // Deactivate existing QR codes
        QrCode::where('schedule_id', $this->scheduleId)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Generate new QR code
        $token = Str::random(32);
        $expiresAt = Carbon::now()->addSeconds(6);

        $qrCode = QrCode::create([
            'schedule_id' => $this->scheduleId,
            'token' => $token,
            'meeting_number' => $this->meetingNumber,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        // Prepare QR code data
        $qrData = [
            'token' => $token,
            'subject_id' => $schedule->subject_id,
            'class_id' => $schedule->class_id,
            'teacher_id' => $schedule->teacher_id,
            'meeting_number' => $this->meetingNumber,
            'expires_at' => $expiresAt->toIso8601String(),
        ];

        // Broadcast the event
        event(new QrCodeGenerated(json_encode($qrData), $this->scheduleId));

        // Queue the next QR code generation
        GenerateQrCode::dispatch($this->scheduleId, $this->meetingNumber)
            ->delay(now()->addSeconds(6));
    }
}