<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CarSold extends Notification
{
    use Queueable;

    public $auction;
    public $winner;

    public function __construct($auction, $winner)
    {
        $this->auction = $auction;
        $this->winner = $winner;
    }

    public function via($notifiable) { return ['database']; }

    public function toArray($notifiable)
    {
        return [
            'message' => 'سيارتك ' . $this->auction->car->brand . ' ' . $this->auction->car->model . ' انباعت لـ ' . $this->winner->user->name . ' بمبلغ ' . $this->winner->amount
        ];
    }
}