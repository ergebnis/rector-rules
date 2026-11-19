<?php

namespace App\Http;

use const App\Config\TIMEOUT as LIMIT;

final class Router
{
    public function dashboard(): int
    {
        return LIMIT + timeout;
    }
}

?>
-----
<?php

namespace App\Http;

use const App\Config\TIMEOUT;

final class Router
{
    public function dashboard(): int
    {
        return TIMEOUT + timeout;
    }
}

?>
