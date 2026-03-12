<?php

namespace Example\App;

use Example\Core\Contract\Arrayable;
use Example\Core\Contract\Jsonable;
use Example\Core\Event\ExampleEvent;
use Psr\Log\LoggerInterface;

final class ExampleEntity implements Arrayable, Jsonable
{
    public function dispatch(): void
    {
        $class = ExampleEvent::class;
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;
use Psr\Log\LoggerInterface;

final class ExampleEntity implements Core\Contract\Arrayable, Core\Contract\Jsonable
{
    public function dispatch(): void
    {
        $class = Core\Event\ExampleEvent::class;
    }
}

?>
