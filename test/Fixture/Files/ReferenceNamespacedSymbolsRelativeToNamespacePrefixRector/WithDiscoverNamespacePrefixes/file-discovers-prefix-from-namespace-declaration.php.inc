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

final class RequestHandler
{
    public function handle(
        Request $request,
        Session\Session $session,
        File\UploadedFile $file
    ): Response {
        return new Response();
    }
}

?>
