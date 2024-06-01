<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\AdminResetPasswordMail;

class SendAdminResetPasswordEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        try {
            Mail::to($this->data['admin']->email)->send(new AdminResetPasswordMail($this->data));
        } catch (\Exception $e) {
            Log::error('Mail send failed: ' . $e->getMessage());
            Log::error('Mail send failed trace: ' . $e->getTraceAsString());
            throw $e;
        }

    }
}
