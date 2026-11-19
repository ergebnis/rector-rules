<?php

namespace App\Http;

use function App\Helper\render as view;
use const App\Config\TIMEOUT as LIMIT;

final class Router
{
    public function dashboard(): string
    {
        return view(LIMIT);
    }
}

?>
-----
<?php

namespace App\Http;

use function App\Helper\render;
use const App\Config\TIMEOUT;

final class Router
{
    public function dashboard(): string
    {
        return render(TIMEOUT);
    }
}

?>
