<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

Broadcast::channel('qrcode.{scheduleId}', function ($user, $scheduleId) {
    // Only teachers and admins can listen to QR code channels
    if ($user->isAdmin()) {
        return true;
    }
    
    if ($user->isTeacher() && $user->teacher) {
        $schedule = \App\Models\Schedule::find($scheduleId);
        return $schedule && $schedule->teacher_id === $user->teacher->id;
    }
    
    return false;
});

