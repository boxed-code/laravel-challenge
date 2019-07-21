<?php

namespace BoxedCode\Laravel\TwoFactor\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DefaultAuthenticationRequest extends Notification implements ShouldQueue
{
    use Queueable;

    protected $code;

    protected $channels;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($code, array $channels)
    {
        $this->code = $code;

        $this->channels = $channels;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->channels;
    }

    /**
     * Get the notification's content.
     *
     * @return sting
     */
    protected function getNotificationContent()
    {
        return 'Your authentication code is: ' . $this->code . '.';
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                ->line($this->getNotificationContent());
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
            'message' => $this->getNotificationContent()
        ];
    }
}