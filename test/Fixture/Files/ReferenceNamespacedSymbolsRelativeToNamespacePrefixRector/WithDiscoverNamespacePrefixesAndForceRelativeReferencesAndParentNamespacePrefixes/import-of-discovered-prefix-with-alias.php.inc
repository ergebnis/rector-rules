<?php

namespace LTP\Outside\Infrastructure\Mail;

use Symfony\Component\Mailer as Foo;

final class SymfonyMailer implements Mailer
{
    /**
     * @var Foo\Transport\TransportInterface
     */
    private $transport;

    public function __construct(Foo\Transport\TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function send(Foo\Envelope $envelope)
    {
    }
}

?>
-----
<?php

namespace LTP\Outside\Infrastructure\Mail;

use LTP\Outside;
use Symfony\Component\Mailer as Foo;

final class SymfonyMailer implements Outside\Infrastructure\Mail\Mailer
{
    /**
     * @var Foo\Transport\TransportInterface
     */
    private $transport;

    public function __construct(Foo\Transport\TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function send(Foo\Envelope $envelope)
    {
    }
}

?>
