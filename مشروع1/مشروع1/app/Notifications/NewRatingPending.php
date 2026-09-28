<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRatingPending extends Notification
{
    use Queueable;

    public $car;
    public $user;

    public function __construct($car, $user)
    {
        $this->car = $car;
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'تقييم جديد من ' . $this->user->name . ' على سيارة ' . $this->car->brand . ' ' . $this->car->model . ' بانتظار الموافقة'
        ];
    }
}