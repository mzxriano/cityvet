<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RoleRequestRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $role;
    public $messageText;

    public function __construct($user, $role, $messageText)
    {
        $this->user = $user;
        $this->role = $role;
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->subject('Your Role Request Has Been Rejected')
            ->view('emails.role_request_rejected');
    }
}
