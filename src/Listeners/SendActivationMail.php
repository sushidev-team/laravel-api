<?php

namespace AMBERSIVE\Api\Listeners;

use Mail;

use AMBERSIVE\Api\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendActivationMail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
       
    }

    /**
     * Handle the event.
     *
     * @param  NotificationCreated  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        Mail::to($event->user->email)->locale($event->user->language)->send(new \AMBERSIVE\Api\Mails\ActivationMail($event->user, $event->code));
        return true;
    }
}
