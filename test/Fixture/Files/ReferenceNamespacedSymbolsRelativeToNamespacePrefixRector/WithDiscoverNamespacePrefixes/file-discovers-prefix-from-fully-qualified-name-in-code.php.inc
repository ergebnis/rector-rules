<?php

namespace App;

final class Kernel
{
    public function handle()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component;
final class Kernel
{
    public function handle()
    {
        $request = new Component\HttpFoundation\Request();
    }
}

?>
