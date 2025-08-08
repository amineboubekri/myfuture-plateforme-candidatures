<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use App\Notifications\CustomVerifyEmailNotification;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Testing email configuration...');
        
        // Display current mail config
        $this->info('Mail Configuration:');
        $this->line('Mailer: ' . Config::get('mail.default'));
        $this->line('Host: ' . Config::get('mail.mailers.smtp.host'));
        $this->line('Port: ' . Config::get('mail.mailers.smtp.port'));
        $this->line('Username: ' . Config::get('mail.mailers.smtp.username'));
        $this->line('Encryption: ' . Config::get('mail.mailers.smtp.encryption'));
        $this->line('From Address: ' . Config::get('mail.from.address'));
        $this->line('From Name: ' . Config::get('mail.from.name'));
        
        try {
            // Test basic email
            Mail::raw('Test email from MyFuture Platform', function ($message) use ($email) {
                $message->to($email)
                       ->from('myfuture.plateform@gmail.com', 'MyFuture Platform')
                       ->subject('Test Email - MyFuture Platform');
            });
            
            $this->info('✅ Basic test email sent successfully to: ' . $email);
            
            // Test verification notification if user exists
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->notify(new CustomVerifyEmailNotification());
                $this->info('✅ Verification email sent successfully to: ' . $email);
            } else {
                $this->info('User not found, only basic test email sent');
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Email sending failed!');
            $this->error('Error: ' . $e->getMessage());
            
            // Log more details
            $this->error('Stack trace:');
            $this->line($e->getTraceAsString());
        }
    }
}
