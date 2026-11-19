<?php

namespace App\Http;

use function App\Controller\Helper\resolve_path;
use const App\Controller\Config\TIMEOUT;

final class Router
{
    public function dashboard(): string
    {
        $timeout = TIMEOUT;

        return resolve_path('/users');
    }
}

?>
