<?php

namespace App;

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
use Symfony\Component\Mailer\{function send};

final class Notifier
{
    public function __construct(Mailer\MailerInterface $mailer)
    {
        send($mailer);
    }
}

?>
