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
    protected $name;

    public function __construct($enable, $country, $name)
    {
        $this->enable = $enable;
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
        if ($this->enable) {
            if (strtolower($this->country) == 'sweden') {
                return (new MailMessage)
                    ->from(config('mail.username'), config('app.name'))
                    ->subject('Du har nu administratörstatus på ditt konto.')
                    ->markdown('vendor.mail.message', [
                        'country' => $this->country,
                        'name' => $this->name,
                        'contents' => [
                            'Du har nu administratörstatus på ditt konto.',
                            'Tack!',
                        ]
                    ]);
            } else {
                return (new MailMessage)
                    ->from(config('mail.username'), config('app.name'))
                    ->subject('A group manager has been added to your group.')
                    ->markdown('vendor.mail.message', [
                        'country' => $this->country,
                        'name' => $this->name,
                        'contents' => [
                            'A group manager has been added to your group.',
                            'Thank you!',
                        ]
                    ]);
            }
        } else {
            if (strtolower($this->country) == 'sweden') {
                return (new MailMessage)
                    ->from(config('mail.username'), config('app.name'))
                    ->subject('Du är inte längre administratör för gruppen.')
                    ->markdown('vendor.mail.message', [
                        'country' => $this->country,
                        'name' => $this->name,
                        'contents' => [
                            'Du är inte längre administratör för gruppen.',
                            'Tack!',
                        ]
                    ]);
            } else {
                return (new MailMessage)
                    ->from(config('mail.username'), config('app.name'))
                    ->subject('A group manager has been deleted to your group.')
                    ->markdown('vendor.mail.message', [
                        'country' => $this->country,
                        'name' => $this->name,
                        'contents' => [
                            'A group manager has been deleted to your group.',
                            'Thank you!',
                        ]
                    ]);
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