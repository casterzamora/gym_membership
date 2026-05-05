<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public $user, public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = rtrim(config('app.frontend_url'), '/') . '/reset-password?email=' . urlencode($this->user->email) . '&token=' . urlencode($this->token);

        return (new MailMessage)
            ->greeting('Password Reset Request')
            ->line('We received a request to reset your password.')
            ->action('Reset Password', $resetUrl)
            ->line('This reset link will expire in 60 minutes.')
            ->line('If you did not request a password reset, no further action is required.')
            ->salutation('Best regards, GymFlow Team');
    }
}
