<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Car;
use App\Models\User;

class CarRated extends Notification
{
    use Queueable;

    public $car;
    public $user;
    public $rating;

    public function __construct(Car $car, User $user, $rating)
    {
        $this->car = $car;
        $this->user = $user;
        $this->rating = $rating;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'car_id' => $this->car->id,
            'car_brand' => $this->car->brand,
            'car_model' => $this->car->model,
            'user_name' => $this->user->name,
            'rating' => $this->rating,
            'message' => $this->user->name . ' قيّم سيارتك ' . $this->car->brand . ' ' . $this->car->model . ' بـ ' . $this->rating . ' نجوم',
        ];
    }
}