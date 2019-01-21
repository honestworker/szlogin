<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $user;
    protected $country;
    protected $name;

    public function __construct($user, $country, $name)
    {
        $this->user = $user;
        $this->country = $country;
        $this->name = $name;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {        
        $url = url('/');
        
        if (strtolower($this->country) == 'sweden') {
            return (new MailMessage)
                ->from(config('mail.username'), config('app.name'))
                ->subject('Ditt lösenord har återställts framgångsrikt!')
                ->markdown('vendor.mail.message', [
                    'country' => $this->country,
                    'name' => $this->name,
                    'contents' => [
                        'Ditt lösenord har återställts!',
                        'Tack!',
                    ]
                ]);
        } else {
            return (new MailMessage)
                ->from(config('mail.username'), config('app.name'))
                ->subject('Your password has been reset successfully!')
                ->markdown('vendor.mail.message', [
                    'country' => $this->country,
                    'name' => $this->name,
                    'contents' => [
                        'Your password has been reset successfully!',
                        'Thank you!',
                    ]
                ]);
        }
    }
    
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

