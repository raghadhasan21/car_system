<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBid extends Notification
{
    use Queueable;

    public $auction;
    public $bid;

    public function __construct($auction, $bid)
    {
        $this->auction = $auction;
        $this->bid = $bid;
    }

    public function via($notifiable) { return ['database']; }

    public function toArray($notifiable)
    {
        return [
            'message' => 'مزايدة جديدة على ' . $this->auction->car->brand . ' ' . $this->auction->car->model . ' بمبلغ ' . $this->bid->amount
        ];
    }
}