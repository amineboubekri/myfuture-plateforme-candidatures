<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomVerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'date_of_birth',
        'is_approved',
        'email_verified_at',
        'profile_completed',
        'google2fa_secret',
        'google2fa_enabled',
        'google2fa_enabled_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'google2fa_enabled' => 'boolean',
        'google2fa_enabled_at' => 'datetime',
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if the user's profile is complete
     */
    public function isProfileComplete()
    {
        return !empty($this->phone) && 
               !empty($this->address) && 
               !empty($this->date_of_birth) &&
               $this->profile_completed;
    }

    /**
     * Mark profile as complete if all required fields are filled
     */
    public function updateProfileCompletionStatus()
    {
        $isComplete = !empty($this->phone) && 
                     !empty($this->address) && 
                     !empty($this->date_of_birth);
        
        $this->update(['profile_completed' => $isComplete]);
        
        return $isComplete;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmailNotification);
    }
}
