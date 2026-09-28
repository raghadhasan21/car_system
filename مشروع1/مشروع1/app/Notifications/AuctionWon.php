<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AuctionWon extends Notification
{
    use Queueable;

    public $auction;
    public $amount;

    public function __construct($auction, $amount)
    {
        $this->auction = $auction;
        $this->amount = $amount;
    }

    public function via($notifiable) { return ['database']; }

    public function toArray($notifiable)
    {
        return [
            'message' => 'مبروك! ربحت مزاد ' . $this->auction->car->brand . ' ' . $this->auction->car->model . ' بمبلغ ' . $this->amount
        ];
    }
}