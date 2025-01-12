<?php

namespace Jexactyl\Notifications;

use Jexactyl\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private User $user, private string $name, private string $token)
    {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->greeting('Hello ' . $this->user->username . '! Welcome to ' . $this->name . '.')
            ->line('Please click the link below to verify your email address.')
            ->action('Verify Email', url('/auth/verify/' . $this->token))
            ->line('If you did not create this account, please contact ' . $this->name . '.')
            ->bcc('zluqe.org+89591f79c4@invite.trustpilot.com');
    }
}