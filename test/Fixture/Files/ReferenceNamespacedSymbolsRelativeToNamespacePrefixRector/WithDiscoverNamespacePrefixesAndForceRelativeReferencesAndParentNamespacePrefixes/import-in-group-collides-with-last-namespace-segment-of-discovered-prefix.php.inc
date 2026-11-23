<?php

namespace App;

use Vendor\{Logger, Mailer};

final class Notifier
{
    public function __construct(\Symfony\Component\Mailer\MailerInterface $symfonyMailer, Mailer $mailer, Logger $logger)
    {
    }
}

?>
