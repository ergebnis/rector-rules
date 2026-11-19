<?php

namespace App\Http;

use const App\Config\TIMEOUT as LIMIT;

final class Router
{
    public function dashboard(): int
    {
        return LIMIT + TIMEOUT;
    }
}

?>
