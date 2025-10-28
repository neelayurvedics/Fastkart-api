<?php

namespace App\Listeners;

use Exception;
use App\Helpers\Helpers;
use App\Mail\UpdateOrderStatusMail;
use Illuminate\Support\Facades\Mail;
use App\Events\UpdateOrderStatusEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\UpdateOrderStatusNotification;

class UpdateOrderStatusListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(UpdateOrderStatusEvent $event)
    {
        try {

            if ($event->order->consumer && is_null($event->order->consumer_id)) {
                $this->sendGuestEmail($event->order->consumer, $event->order);
            }

            if ($event->order->consumer_id) {
                $consumer = Helpers::getConsumerById($event->order->consumer_id);
                if ($consumer) {
                    $consumer->notify(new UpdateOrderStatusNotification($event->order, $consumer));
                }
            }

        } catch (Exception $e) {

            //
        }
    }

    public function sendGuestEmail($consumer, $order)
    {
        Mail::to($consumer['email'])->send(new UpdateOrderStatusMail($order));
    }
}
