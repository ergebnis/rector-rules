<?php

namespace App;

final class Notifier
{
    use \Symfony\Component\Mailer\HasMailer;
}

?>
-----
<?php

namespace App;

use Symfony\Component\Mailer;

final class Notifier
{
    use Mailer\HasMailer;
}

?>
