<?php

namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class RequestHandler
{
    public function handle(
        Request $request,
        Session $session,
        UploadedFile $file
    ): Response {
        return new Response();
    }
}

?>
-----
<?php

namespace Symfony\Component\HttpFoundation;

use Symfony\Component;

final class RequestHandler
{
    public function handle(
        Component\HttpFoundation\Request $request,
        Component\HttpFoundation\Session\Session $session,
        Component\HttpFoundation\File\UploadedFile $file
    ): Component\HttpFoundation\Response {
        return new Component\HttpFoundation\Response();
    }
}

?>
