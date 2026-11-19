<?php

namespace App\Http;

use App\Helper\{function render as view, const TIMEOUT as LIMIT};
use function App\Format\{escape as e};

final class Router
{
    public function dashboard(): string
    {
        return view(e(LIMIT));
    }
}

?>
-----
<?php

namespace App\Http;

use App\Helper\{function render, const TIMEOUT};
use function App\Format\{escape};

final class Router
{
    public function dashboard(): string
    {
        return render(escape(TIMEOUT));
    }
}

?>
