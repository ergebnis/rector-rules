<?php

namespace App;

final class Notifier
{
    use Mailer\HasMailer;

    public function __construct(\Symfony\Component\Mailer\MailerInterface $mailer)
    {
    }
}

?>
