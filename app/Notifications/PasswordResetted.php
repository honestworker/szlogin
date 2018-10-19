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

    public function __construct($user, $country)
    {
        $this->user = $user;
        $this->country = $country;
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

        if ($this->country == 'Sweden') {
            return (new MailMessage)
                        ->greeting('Hello!')
                        ->line('Your password has been reset successfully!')
                        ->line('Click on the below link to continue login.')
                        ->action('Login', $url);
        } else {
            return (new MailMessage)
                        ->greeting('Hello!')
                        ->line('Ditt lösenord har återställts!')
                        ->line('Klicka på länken nedan för att fortsätta logga in.')
                        ->action('Logga in', $url);
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
