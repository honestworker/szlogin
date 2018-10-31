<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class Administrator extends Notification
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
            if ($this->country == 'Sweden') {
                return (new MailMessage)
                    ->from(config('mail.username'), config('app.name'))
                    ->subject('Conguratulations!')
                    ->markdown('vendor.mail.message', [
                        'country' => $this->country,
                        'name' => $this->name,
                        'contents' => [
                            'You have gotten the administrator permission of our site!',
                        ]
                    ]);
            } else {
                return (new MailMessage)
                    ->from(config('mail.username'), config('app.name'))
                    ->subject('Conguratulations!')
                    ->markdown('vendor.mail.message', [
                        'country' => $this->country,
                        'name' => $this->name,
                        'contents' => [
                            'You have gotten the administrator permission of our site!',
                        ]
                    ]);
            }
        } else {
            if ($this->country == 'Sweden') {
                return (new MailMessage)
                    ->from(config('mail.username'), config('app.name'))
                    ->subject('Sorry!')
                    ->markdown('vendor.mail.message', [
                        'country' => $this->country,
                        'name' => $this->name,
                        'contents' => [
                            'You have lost the administrator permission of our site!',
                        ]
                    ]);
            } else {
                return (new MailMessage)
                    ->from(config('mail.username'), config('app.name'))
                    ->subject('Sorry!')
                    ->markdown('vendor.mail.message', [
                        'country' => $this->country,
                        'name' => $this->name,
                        'contents' => [
                            'You have lost the administrator permission of our site!',
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
