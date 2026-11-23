<?php

declare(strict_types=1);

final class Finder
{
    public function find(\App\Controller\User\FindAction $action): void
    {
    }
}

?>
-----
<?php

declare(strict_types=1);

use App\Controller;

final class Finder
{
    public function find(Controller\User\FindAction $action): void
    {
    }
}

?>
