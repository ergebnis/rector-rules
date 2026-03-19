<?php

namespace App;

final class Kernel
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function handle($request)
    {
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\HttpFoundation;
final class Kernel
{
    /**
     * @param HttpFoundation\Request $request
     */
    public function handle($request)
    {
    }
}

?>
