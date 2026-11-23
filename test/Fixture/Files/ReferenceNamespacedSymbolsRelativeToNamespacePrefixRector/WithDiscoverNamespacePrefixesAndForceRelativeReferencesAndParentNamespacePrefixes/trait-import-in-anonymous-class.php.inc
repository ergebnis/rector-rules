<?php

namespace App;

final class NotifierFactory
{
    public function create(): object
    {
        return new class {
            use \Symfony\Component\Mailer\HasMailer;
        };
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\Mailer;
final class NotifierFactory
{
    public function create(): object
    {
        return new class {
            use Mailer\HasMailer;
        };
    }
}

?>
