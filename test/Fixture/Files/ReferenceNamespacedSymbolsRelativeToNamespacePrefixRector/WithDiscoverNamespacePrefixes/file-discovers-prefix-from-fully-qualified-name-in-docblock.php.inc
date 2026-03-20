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

use Symfony\Component;
final class Kernel
{
    /**
     * @param Component\HttpFoundation\Request $request
     */
    public function handle($request)
    {
    }
}

?>
