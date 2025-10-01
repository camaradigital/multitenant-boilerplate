<?php

namespace App\Notifications\Tenant;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Spatie\Multitenancy\Models\Tenant;

class VerifyTenantEmail extends VerifyEmailBase implements ShouldQueue
{
    use Queueable;

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $tenant = Tenant::current();
        if (! $tenant) {
            return '';
        }

        $domain = $tenant->subdomain.'.'.config('app.central_domain');
        $prefix = config('app.env') === 'production' ? 'https' : 'http';

        $temporarySignedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return str_replace(url('/'), $prefix.'://'.$domain, $temporarySignedUrl);
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $tenantName = Tenant::current()?->name ?? config('app.name');

        return (new MailMessage)
            ->subject('Verifique seu Endereço de E-mail - '.$tenantName)
            ->view(
                'emails.tenant.verify-email',
                [
                    'url' => $verificationUrl,
                    'userName' => $notifiable->name,
                    'tenantName' => $tenantName,
                ]
            );
    }
}
