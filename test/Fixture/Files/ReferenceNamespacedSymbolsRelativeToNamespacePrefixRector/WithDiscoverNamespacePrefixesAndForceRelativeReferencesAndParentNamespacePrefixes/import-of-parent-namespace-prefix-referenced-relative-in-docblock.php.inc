<?php

namespace App;

use Symfony\Component;

final class RequestHolder
{
    /**
     * @var Component\HttpFoundation\Request
     */
    private $request;
}

?>
-----
<?php

namespace App;

use Symfony\Component;
use Symfony\Component\HttpFoundation;

final class RequestHolder
{
    /**
     * @var HttpFoundation\Request
     */
    private $request;
}

?>
