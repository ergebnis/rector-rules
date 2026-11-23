<?php

namespace App;

// Imports from the mailer.
use Symfony\Component\Mailer\{MailerInterface, function send};

final class Notifier
{
    public function __construct(MailerInterface $mailer)
    {
        send($mailer);
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\Mailer;
// Imports from the mailer.
use Symfony\Component\Mailer\{function send};

final class Notifier
{
    public function __construct(Mailer\MailerInterface $mailer)
    {
        send($mailer);
    }
}

?>
