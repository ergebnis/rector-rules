<?php

namespace App\Http {
    use App\Controller as Foo;

    final class Router
    {
        public function dashboard(): Foo\DashboardController
        {
        }
    }
}

namespace App\Console {
    use App\Command as Bar;

    final class Kernel
    {
        public function command(): Bar\ListCommand
        {
        }
    }
}

?>
-----
<?php

namespace App\Http {
    use App\Controller;

    final class Router
    {
        public function dashboard(): Controller\DashboardController
        {
        }
    }
}

namespace App\Console {
    use App\Command;

    final class Kernel
    {
        public function command(): Command\ListCommand
        {
        }
    }
}

?>
