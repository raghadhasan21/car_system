<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewAuction extends Notification
{
    use Queueable;

    public $auction;

    public function __construct($auction)
    {
        $this->auction = $auction;
    }

    public function via($notifiable) { return ['database']; }

    public function toArray($notifiable)
    {
        return [
            'message' => 'مزاد جديد على ' . $this->auction->car->brand . ' ' . $this->auction->car->model
        ];
    }
}