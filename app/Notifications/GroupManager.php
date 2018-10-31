<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class GroupManager extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $enable;
    protected $country;

    public function __construct($enable, $country)
    {
        $this->enable = $enable;
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
        if ($this->enable) {
            if ($this->country == 'Sweden') {
                return (new MailMessage)
                    ->greeting('Hey!')
                    ->line('Du har nu administratörstatus på ditt konto.');
            } else {
                return (new MailMessage)
                    ->greeting('Hello!')
                    ->line('A group manager has been added to your group.')
                    ->line('')
                    ->line('Best regards,')
                    ->line('Safety Zone');
            }
        } else {
            if ($this->country == 'Sweden') {
                return (new MailMessage)
                        ->greeting('Hey!')
                        ->line('Du är inte längre administratör för gruppen.');
            } else {
                return (new MailMessage)
                        ->greeting('Hello!')
                        ->line('A group manager has been deleted to your group.')
                        ->line('')
                        ->line('Best regards,')
                        ->line('Safety Zone');
            }
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
