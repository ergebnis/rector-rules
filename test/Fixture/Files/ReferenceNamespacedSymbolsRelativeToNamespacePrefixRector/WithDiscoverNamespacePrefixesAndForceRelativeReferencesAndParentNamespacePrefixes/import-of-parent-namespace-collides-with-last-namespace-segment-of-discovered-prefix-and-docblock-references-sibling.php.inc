<?php

namespace App;

use Smarty;

final class View
{
    /**
     * @throws Smarty\Exception
     */
    public function __construct(Smarty\Smarty $smarty)
    {
    }
}

?>
-----
<?php

namespace App;

use Smarty\Exception;
use Smarty\Smarty;

final class View
{
    /**
     * @throws Exception
     */
    public function __construct(Smarty $smarty)
    {
    }
}

?>
