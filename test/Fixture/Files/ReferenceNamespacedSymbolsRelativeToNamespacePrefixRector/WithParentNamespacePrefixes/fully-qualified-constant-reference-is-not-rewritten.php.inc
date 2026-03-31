<?php

namespace App;

use Symfony\Component\HttpFoundation\Response;

final class Kernel
{
    public function handle(): Response
    {
        $value = \Foo\Bar\SOME_CONSTANT;
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
        $value = \Foo\Bar\SOME_CONSTANT;
    }
}

?>
