<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates an Admin User';

    /**
     * Execute the console command.
     */
    public function handle() {

        $email = $this->ask('What is the E-Mail?');
        $password = $this->secret("What is the Password?");
        $first_name = $this->ask('What is the firstname?', 'Vorname');
        $last_name = $this->ask('What is the lastname?', 'Nachname');

        if(User::where("email", $email)->exists()) {
            $this->error("User with email $email already exists");
            return;
        }

        $user = new User();
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->save();

        $user->assignRole('admin');

        $this->info("Admin user with email $email created successfully");
        return 0;

    }
}
