<?php

namespace App;

final class Kernel
{
    public function handle()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();
        $other = new \Other\Vendor\Service();
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\HttpFoundation;
final class Kernel
{
    public function handle()
    {
        $request = new HttpFoundation\Request();
        $other = new \Other\Vendor\Service();
    }
}

?>
