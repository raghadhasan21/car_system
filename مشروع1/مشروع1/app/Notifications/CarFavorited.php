<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Car;
use App\Models\User;

class CarFavorited extends Notification
{
    use Queueable;

    public $car;
    public $user;

    public function __construct(Car $car, User $user)
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
            'car_id' => $this->car->id,
            'car_brand' => $this->car->brand,
            'car_model' => $this->car->model,
            'user_name' => $this->user->name,
            'message' => $this->user->name . ' أضاف سيارتك ' . $this->car->brand . ' ' . $this->car->model . ' إلى المفضلة',
        ];
    }
}