<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerified extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		
		return $this->view('email.email_verified')->from('admin@olympiaLiquor.com', 'Olympia Liquor')
        ->subject('Verify your email')
        ->replyTo('admin@olympiaLiquor.com', 'Olympia Liquor')->with([
            'user' => $this->user,
          ]);
    }
}
