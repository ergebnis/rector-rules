<?php

namespace App;

use Vendor\mailer;

final class Notifier
{
    public function __construct(\Symfony\Component\Mailer\MailerInterface $symfonyMailer, mailer $mailer)
    {
    }
}

?>
