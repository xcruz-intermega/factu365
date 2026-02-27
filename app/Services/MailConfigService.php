<?php

namespace App\Services;

use App\Models\MailConfiguration;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class MailConfigService
{
    public function isActive(): bool
    {
        $config = MailConfiguration::current();

        return $config && $config->is_active && $config->isConfigured();
    }

    public function applyConfig(): void
    {
        $config = MailConfiguration::current();

        if (!$config || !$config->is_active || !$config->isConfigured()) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $config->host,
            'mail.mailers.smtp.port' => $config->port,
            'mail.mailers.smtp.username' => $config->username,
            'mail.mailers.smtp.password' => $config->password,
            'mail.mailers.smtp.encryption' => $config->encryption,
            'mail.from.address' => $config->from_address,
            'mail.from.name' => $config->from_name,
        ]);
    }

    /**
     * Test SMTP connection without sending an email.
     *
     * @return true|string True on success, error message on failure.
     */
    public function testConnection(MailConfiguration $config): true|string
    {
        try {
            $tls = !empty($config->encryption); // true for tls/ssl, false for none
            $transport = new EsmtpTransport(
                $config->host,
                $config->port,
                $tls,
            );

            if ($config->username) {
                $transport->setUsername($config->username);
            }
            if ($config->password) {
                $transport->setPassword($config->password);
            }

            $transport->start();
            $transport->stop();

            return true;
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }
}
