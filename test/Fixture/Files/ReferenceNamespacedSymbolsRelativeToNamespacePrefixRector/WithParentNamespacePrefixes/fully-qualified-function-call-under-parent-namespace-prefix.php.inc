<?php

namespace App;

use Symfony\Component\Clock\DatePoint;

final class Kernel
{
    public function handle(): DatePoint
    {
        return \Symfony\Component\Clock\now();
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\Clock;

final class Kernel
{
    public function handle(): Clock\DatePoint
    {
        return Clock\now();
    }
}

?>
