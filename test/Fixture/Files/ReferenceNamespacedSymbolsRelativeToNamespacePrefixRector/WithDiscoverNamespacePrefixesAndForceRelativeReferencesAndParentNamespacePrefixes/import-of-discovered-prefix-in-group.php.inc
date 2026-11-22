<?php

namespace App;

use Symfony\Component\{Console, Mailer};

final class Notifier
{
    public function __construct(\Symfony\Component\Mailer\MailerInterface $mailer, Console\Application $application)
    {
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\{Console, Mailer};

final class Notifier
{
    public function __construct(Mailer\MailerInterface $mailer, Console\Application $application)
    {
    }
}

?>
