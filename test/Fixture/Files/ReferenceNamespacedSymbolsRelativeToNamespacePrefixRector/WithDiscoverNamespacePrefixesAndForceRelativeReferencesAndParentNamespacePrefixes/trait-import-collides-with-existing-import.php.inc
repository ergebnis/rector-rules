<?php

namespace App;

use Vendor\Mailer;

final class Notifier
{
    use \Symfony\Component\Mailer\HasMailer;

    public function __construct(Mailer $mailer)
    {
    }
}

?>
