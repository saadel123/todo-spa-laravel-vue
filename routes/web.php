<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

use Illuminate\Notifications\Messages\MailMessage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug-mail', function() {
    \Log::info('Starting mail debug at '.now());

    $user = User::first();
    if (!$user) {
        return response()->json(['error' => 'No users found'], 404);
    }

    // Create a test todo
    $todo = $user->todos()->create([
        'title' => 'DEBUG REMINDER',
        'reminder_at' => now()->addHour(),
        'reminded_at' => null
    ]);

    \Log::debug('Test todo created', $todo->toArray());

    try {
        // Bypass queues for testing
        \Mail::raw('RAW EMAIL TEST', function($message) use ($user) {
            $message->to($user->email)->subject('RAW TEST');
        });
        \Log::info('Raw email sent successfully');

        // Test notification
        $user->notifyNow(new \App\Notifications\TodoReminderNotification($todo));
        \Log::info('Notification sent immediately');

        return response()->json([
            'status' => 'success',
            'raw_email' => 'sent',
            'notification' => 'sent',
            'logs' => 'Check storage/logs/laravel.log'
        ]);

    } catch (\Exception $e) {
        \Log::error('Mail test failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => env('APP_DEBUG') ? $e->getTrace() : 'Hidden in production'
        ], 500);
    }
});

Route::get('/test-fake-reminder', function() {
    $user = User::first();

    // Create a test todo with reminder in 5 minutes
    $todo = $user->todos()->create([
        'title' => 'TEST REMINDER',
        'reminder_at' => now()->addMinutes(5),
        'reminded_at' => null
    ]);

    try {
        $user->notify(new App\Notifications\TodoReminderNotification($todo));
        return [
            'status' => 'success',
            'message' => 'Test reminder sent! Check Mailtrap',
            'todo' => [
                'id' => $todo->id,
                'title' => $todo->title,
                'reminder_at' => $todo->reminder_at->format('Y-m-d H:i:s'),
                'user_email' => $user->email
            ]
        ];
    } catch (\Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
});

Route::get('/test-notification', function() {
    $user = User::first();
    $todo = $user->todos()->whereNotNull('reminder_at')->first();

    if (!$todo) {
        $todo = $user->todos()->create([
            'title' => 'TEST REMINDER',
            'reminder_at' => now()->addHour()
        ]);
    }

    try {
        $user->notify(new App\Notifications\TodoReminderNotification($todo));
        return [
            'status' => 'success',
            'user' => $user->email,
            'todo' => $todo->title,
            'reminder_at' => $todo->reminder_at
        ];
    } catch (\Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
});

Route::get('/check-todo', function() {
    $todo = App\Models\Todo::first();
    return [
        'todo_exists' => !!$todo,
        'reminder_at' => $todo?->reminder_at,
        'reminder_at_formatted' => $todo?->reminder_at?->format('Y-m-d H:i:s'),
        'is_past' => $todo?->reminder_at?->isPast()
    ];
});

Route::get('/test-email', function() {
    $user = User::first();

    if (!$user) {
        return "No users found in database!";
    }

    try {
        // Send a simple notification directly
        $user->notify(new class extends \Illuminate\Notifications\Notification {
            public function via($notifiable)
            {
                return ['mail'];
            }

            public function toMail($notifiable)
            {
                return (new MailMessage)
                    ->subject('Test Email from Laravel')
                    ->line('This is a simple test email from your application!')
                    ->line('If you receive this, your email setup is working.')
                    ->action('Visit App', url('/'));
            }
        });

        return "Basic test email sent to {$user->email}";
    } catch (\Exception $e) {
        return "Error sending email: " . $e->getMessage();
    }
});