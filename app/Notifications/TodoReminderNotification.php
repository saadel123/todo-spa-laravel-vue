<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Todo;

class TodoReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Todo $todo)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
         // Log notification attempt for auditing
        logger()->info('Sending todo reminder', [
            'to' => $notifiable->email,
            'todo_id' => $this->todo->id,
        ]);

        try {
            // Email message
            return (new MailMessage)
                ->subject("Reminder: {$this->todo->title}")
                ->greeting("Hello {$notifiable->name}!")
                ->line("This is a reminder for your upcoming task:")
                ->line("**Task:** {$this->todo->title}")
                ->line("**Due:** {$this->todo->reminder_at->format('l, F jS \a\t g:i A')}")
                ->action('View Task', url('/todos'));

        } catch (\Exception $e) {
            // Log any errors during email construction
            logger()->error('Failed to build reminder email', [
                'error' => $e->getMessage(),
            ]);
            throw $e; // Re-throw to handle in calling code
        }
    }
}