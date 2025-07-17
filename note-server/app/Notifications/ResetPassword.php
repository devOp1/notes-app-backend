<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ResetPassword extends ResetPasswordNotification
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$createUrlCallback) {
            $url = call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        } else {
            $url = env('APP_FRONTEND_URL') . '/password-reset?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
        }

        return (new MailMessage)
            ->subject(Lang::get('Passwort zurücksetzen'))
            ->line(Lang::get('Sie erhalten diese E-Mail, weil wir eine Anfrage zum Zurücksetzen des Passworts für Ihr Konto erhalten haben.'))
            ->action(Lang::get('Passwort zurücksetzen'), $url)
            ->line(Lang::get('Dieser Link zum Zurücksetzen des Passworts läuft in :count Minuten ab.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('Wenn Sie kein Zurücksetzen des Passworts angefordert haben, ist keine weitere Aktion erforderlich.'));
    }
}
