<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Resend\Laravel\Facades\Resend;

class TestMailer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'girasole:mailer:test {--to= : The email address to send the test email to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify mail configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Resend::emails()->send([
            'from' => config('mail.from.address'),
            'to' => [$this->option('to')],
            'subject' => 'hello world',
            'html' => 'Test email from Girasole Farm',
        ]);
    }
}
