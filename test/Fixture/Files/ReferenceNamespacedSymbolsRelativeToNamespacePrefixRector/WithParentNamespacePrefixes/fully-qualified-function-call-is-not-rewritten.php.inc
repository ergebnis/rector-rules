<?php

namespace App;

use Symfony\Component\HttpFoundation\Response;

final class Kernel
{
    public function handle(): Response
    {
        \Safe\unlink('/tmp/file.txt');
        \Safe\file_get_contents('/tmp/file.txt');
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\HttpFoundation;

final class Kernel
{
    public function handle(): HttpFoundation\Response
    {
        \Safe\unlink('/tmp/file.txt');
        \Safe\file_get_contents('/tmp/file.txt');
    }
}

?>
