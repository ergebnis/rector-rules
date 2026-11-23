<?php

namespace App;

use Symfony\Component\Mailer\HasMailer;
use Symfony\Component\Mailer\HasTransport;

final class Notifier
{
    use HasMailer, HasTransport {
        HasMailer::send insteadof HasTransport;
        HasTransport::send as sendWithTransport;
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
        Mailer\HasTransport::send as sendWithTransport;
    }
}

?>
