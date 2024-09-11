<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// use Mail;
use Illuminate\Support\Facades\Mail;
use App\Mail\ErrorReport;

class ReportError extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:report-error {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Internal Command to Report an Error';

    /**
     * Execute the console command.
     */
    public function handle() {
        echo $this->argument('message');
        Mail::to(env("MAIL_TO_ADDRESS"))
            ->send(new ErrorReport( $this->argument('message') ));
    }
}
