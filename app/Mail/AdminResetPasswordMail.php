<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AdminResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Data for the email template.
     *
     * @var array
     */
    public $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('mohamed@gmail','mo')
                    ->view('email-templates.admin-forgot-email-template')
                    ->with($this->data)
                    ->subject('Reset Password');
    }

}
