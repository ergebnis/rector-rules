<?php

namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Session\Session;
use Psr\Log\LoggerInterface;

final class RequestHandler
{
    public function handle(
        Request $request,
        Session $session,
        LoggerInterface $logger,
    ): Response {
        return new Response();
    }
}

?>
-----
<?php

namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation;
use Psr\Log;

final class RequestHandler
{
    public function handle(
        HttpFoundation\Request $request,
        HttpFoundation\Session\Session $session,
        Log\LoggerInterface $logger,
    ): HttpFoundation\Response {
        return new HttpFoundation\Response();
    }
}

?>
