<?php

namespace App;

final class Notifier
{
    use \Symfony\Component\Mailer\HasMailer, \Symfony\Component\Mailer\HasTransport {
        \Symfony\Component\Mailer\HasMailer::send insteadof \Symfony\Component\Mailer\HasTransport;
        \Symfony\Component\Mailer\HasTransport::send as protected sendWithTransport;
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\Mailer;
final class Notifier
{
    use Mailer\HasMailer, Mailer\HasTransport {
        Mailer\HasMailer::send insteadof Mailer\HasTransport;
        Mailer\HasTransport::send as protected sendWithTransport;
    }
}

?>
