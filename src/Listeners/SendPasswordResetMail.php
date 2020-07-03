<?php

namespace AMBERSIVE\Api\Listeners;

use Mail;

use AMBERSIVE\Api\Events\ForgotPassword;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPasswordResetMail
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
    public function handle(ForgotPassword $event)
    {
        Mail::to($event->user->email)->locale($event->user->language)->send(new \AMBERSIVE\Api\Mails\ResetPasswordMail($event->user, $event->code));
        return true;
    }
}
