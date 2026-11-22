<?php

namespace App;

// Imports from the mailer.
use Symfony\Component\Mailer\{Envelope, MailerInterface};

final class Notifier
{
    public function __construct(MailerInterface $mailer, Envelope $envelope)
    {
    }
}

?>
-----
<?php

namespace App;

// Imports from the mailer.
use Symfony\Component\Mailer;

final class Notifier
{
    public function __construct(Mailer\MailerInterface $mailer, Mailer\Envelope $envelope)
    {
    }
}

?>
