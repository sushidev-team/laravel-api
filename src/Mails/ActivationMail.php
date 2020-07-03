<?php

namespace AMBERSIVE\Api\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private String $code;
    private String $layout;
    private String $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, String $code = null)
    {
       $this->user = $user;
       $this->code = $code;
       $this->url  = route('api.auth.register.activation', ['code' => $code]);

       // Define the layout
       $this->layout = config('mail.layout', 'ambersive-api::layouts.email');

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = $this->view(config('ambersive-mails.activation_code','ambersive-api::emails.activation'), ['layout' => $this->layout, 'user' => $this->user, 'code' => $this->code, 'url' => $this->url]);
        return $view;
    }
}
