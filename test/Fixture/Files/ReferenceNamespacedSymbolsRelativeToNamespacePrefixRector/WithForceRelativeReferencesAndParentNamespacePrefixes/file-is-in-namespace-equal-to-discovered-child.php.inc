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

use Symfony\Component\HttpFoundation;

final class RequestHandler
{
    public function handle(
        HttpFoundation\Request $request,
        HttpFoundation\Session\Session $session,
        HttpFoundation\File\UploadedFile $file
    ): HttpFoundation\Response {
        return new HttpFoundation\Response();
    }
}

?>
