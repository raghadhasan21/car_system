<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url('http://127.0.0.1:8000/api/reset-password?token=' . $this->token . '&email=' . $notifiable->email);

        return (new MailMessage)
            ->subject('إعادة تعيين كلمة المرور')
            ->line('أنت تستلم هذا الإيميل لأننا تلقينا طلب إعادة تعيين كلمة المرور.')
            ->action('إعادة تعيين كلمة المرور', $url)
            ->line('هذا الرابط سينتهي خلال 60 دقيقة.')
            ->line('إذا لم تطلب إعادة تعيين كلمة المرور، لا تقم بأي إجراء.');
    }
}