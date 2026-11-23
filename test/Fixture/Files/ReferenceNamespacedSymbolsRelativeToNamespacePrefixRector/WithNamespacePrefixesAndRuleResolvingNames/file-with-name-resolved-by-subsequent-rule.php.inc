<?php

namespace App\Service;

use App\Controller\User\FindAction;

final class Finder
{
    public function find(FindAction $action): void
    {
    }
}

?>
-----
<?php

namespace App\Service;

use App\Controller;

final class Finder
{
    public function find(Controller\User\FindAction $findAction): void
    {
    }
}

?>
