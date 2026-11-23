<?php

namespace App;

final class Notifier
{
    use \Symfony\Component\Mailer\HasMailer, \Symfony\Component\Mailer\HasTransport;
}

?>
-----
<?php

namespace App;

use Symfony\Component\Mailer;

final class Notifier
{
    use Mailer\HasMailer, Mailer\HasTransport;
}

?>
