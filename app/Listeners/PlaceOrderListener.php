<?php

namespace App\Listeners;

use Exception;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Mail\PlaceOrderMail;
use App\Events\PlaceOrderEvent;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\PlaceOrderNotification;

class PlaceOrderListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PlaceOrderEvent $event)
    {
        try {

            if ($event->order->consumer && is_null($event->order->consumer_id)) {
                $this->sendGuestEmail($event->order->consumer, $event->order);
            }

            if ($event->order->consumer_id && is_null($event->order->parent_id)) {
                $consumer = Helpers::getConsumerById($event->order->consumer_id);
                if ($consumer) {
                    $consumer->notify(new PlaceOrderNotification($event->order, RoleEnum::CONSUMER));
                }
            }

            foreach ($event->order->sub_orders as $sub_order) {
                if (isset($sub_order->store_id)) {
                    $vendor = Helpers::getStoreById($sub_order->store_id)?->vendor;
                    $vendor->notify(new PlaceOrderNotification($sub_order, RoleEnum::VENDOR));
                }
            }

            $admin = User::role(RoleEnum::ADMIN)->first();
            if (isset($admin)) {
                $admin->notify(new PlaceOrderNotification($event->order, RoleEnum::ADMIN));
            }

        } catch (Exception $e) {

            //
        }
    }

    public function sendGuestEmail($consumer, $order)
    {
        Mail::to($consumer['email'])->send(new PlaceOrderMail($order));
    }
}
