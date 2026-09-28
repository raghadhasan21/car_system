<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RatingStatusChanged extends Notification
{
    use Queueable;

    public $status;
    public $car;

    public function __construct($status, $car)
    {
        $this->status = $status;
        $this->car = $car;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $msg = $this->status === 'approved' 
            ? 'تمت الموافقة على تقييمك لسيارة ' . $this->car->brand . ' ' . $this->car->model
            : 'تم رفض تقييمك لسيارة ' . $this->car->brand . ' ' . $this->car->model;

        return ['message' => $msg];
    }
}