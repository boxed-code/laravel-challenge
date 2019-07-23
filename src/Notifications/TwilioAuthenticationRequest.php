<?php

namespace BoxedCode\Laravel\TwoFactor\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Twilio\TwilioSmsMessage;

class TwilioAuthenticationRequest extends DefaultAuthenticationRequest implements ShouldQueue
{
    use Queueable;

    public function toTwilio($notifiable)
    {
        return (new TwilioSmsMessage())
            ->content($this->getNotificationPlainText());
    }
}