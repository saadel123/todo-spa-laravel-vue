<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Todo;
use App\Notifications\TodoReminderNotification;
use Illuminate\Support\Facades\Log;

class SendTodoReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send todo reminders to users';

    public function handle()
    {
        Log::info('Starting reminder processing at ' . now());

        $todos = Todo::with('user')
            ->whereNotNull('reminder_at')
            ->where('reminder_at', '<=', now()->addMinutes(15)) // 15 min before due
            ->where('reminder_at', '>=', now()) // Not past due
            ->where('completed', false)  // Excludes completed todos
            ->whereNull('reminded_at') // Not already reminded
            ->get();

        // Process each eligible todo
        foreach ($todos as $todo) {
            try {
                // Send immediate notification to user
                $todo->user->notifyNow(new TodoReminderNotification($todo));
                // Mark as reminded to prevent duplicate notifications
                $todo->update(['reminded_at' => now()]);

            } catch (\Exception $e) {
                Log::error('Failed to send reminder for todo #' . $todo->id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Output completion message to console
        $this->info('Completed: Sent ' . $todos->count() . ' reminders');
        // Log completion of the process
        Log::info('Reminder processing completed');
    }
}