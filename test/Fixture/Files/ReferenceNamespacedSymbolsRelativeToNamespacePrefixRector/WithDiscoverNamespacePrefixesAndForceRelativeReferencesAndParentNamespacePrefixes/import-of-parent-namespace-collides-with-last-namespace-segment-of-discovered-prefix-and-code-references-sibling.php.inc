<?php

namespace App;

use Smarty;

final class View
{
    public function render(Smarty\Smarty $smarty): string
    {
        try {
            return $smarty->fetch('index.tpl');
        } catch (Smarty\Exception $exception) {
            return '';
        }
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
    public function render(Smarty $smarty): string
    {
        try {
            return $smarty->fetch('index.tpl');
        } catch (Exception $exception) {
            return '';
        }
    }
}

?>
