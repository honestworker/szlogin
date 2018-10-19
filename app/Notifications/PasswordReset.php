<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordReset extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $user;
    protected $code;
    protected $country;

    public function __construct($user, $code, $country)
    {
        $this->user = $user;
        $this->code = $code;
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
        $url = url('/password/reset/'.$this->code);

        if ($this->country == 'Sweden') {
            return (new MailMessage)
                        ->greeting('Hej!')
                        ->line('Vi har mottagit begäran om återställning av lösenord från dig!')
                        ->line('Verifierings kod: ' . $this->code . '.')
                        ->line('Tack!');
        } else {            
            return (new MailMessage)
                        ->greeting('Hello!')
                        ->line('We have recevied password reset request from you!')
                        ->line('Verification Code: ' . $this->code . '.')
                        ->line('Thank you!');
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
