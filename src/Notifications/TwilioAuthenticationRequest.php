<?php

namespace BoxedCode\Laravel\Auth\Challenge\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Twilio\TwilioSmsMessage;

class TwilioAuthenticationRequest extends DefaultAuthenticationRequest implements ShouldQueue
{
    use Queueable;

    /**
     * Prepare the notifcation for Twilio.
     *
     * @param Challengeable $notifiable
     *
     * @return NotificationChannels\Twilio\TwilioSmsMessage
     */
    public function toTwilio($notifiable)
    {
        return (new TwilioSmsMessage())
            ->content($this->getNotificationPlainText());
    }
}
