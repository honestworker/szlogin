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
    protected $name;

    public function __construct($user, $code, $country, $name)
    {
        $this->user = $user;
        $this->code = $code;
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
        $url = url('/password/reset/'.$this->code);
        
        if ($this->user) {
            if ($this->user->backend) {
                if (strtolower($this->country) == 'sweden') {
                    return (new MailMessage)
                        ->from(config('mail.username'), config('app.name'))
                        ->subject('Vi har mottagit begäran om återställning av lösenord från dig!')
                        ->markdown('vendor.mail.message', [
                            'country' => $this->country,
                            'name' => $this->name,
                            'contents' => [
                                'Vi hörde att du har förlorat ditt lösenord.',
                                'Men oroa dig inte! Du kan använda följande länk för att återställa ditt lösenord:',
                                $url,
                                'Om du inte använder den här länken inom 30 minuter kommer den att löpa ut.',
                                'Tack!',
                            ]
                        ]);
                } else {
                    return (new MailMessage)
                        ->from(config('mail.username'), config('app.name'))
                        ->subject('Please reset your password!')
                        ->markdown('vendor.mail.message', [
                            'country' => $this->country,
                            'name' => $this->name,
                            'contents' => [
                                'We heard that you lost your password.',
                                'But don’t worry! You can use the following link to reset your password:',
                                $url,
                                'If you don’t use this link within 30 minutes, it will expire.',
                                'Thank you!',
                            ]
                        ]);
                }
            } else {
                if (strtolower($this->country) == 'sweden') {
                    return (new MailMessage)
                        ->from(config('mail.username'), config('app.name'))
                        ->subject('Vi har mottagit begäran om återställning av lösenord från dig!')
                        ->markdown('vendor.mail.message', [
                            'country' => $this->country,
                            'name' => $this->name,
                            'contents' => [
                                'Vi har mottagit begäran om återställning av lösenord från dig!',
                                'Verifierings kod: ' . $this->code . '.',
                                'Om du inte använder den här länken inom 30 minuter kommer den att löpa ut.',
                                'Tack!',
                            ]
                        ]);
                } else {
                    return (new MailMessage)
                        ->from(config('mail.username'), config('app.name'))
                        ->subject('We have recevied password reset request from you!')
                        ->markdown('vendor.mail.message', [
                            'country' => $this->country,
                            'name' => $this->name,
                            'contents' => [
                                'We have recevied password reset request from you!',
                                'Verification Code: ' . $this->code . '.',
                                'If you don’t use this link within 30 minutes, it will expire.',
                                'Thank you!',
                            ]
                        ]);
                }
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
