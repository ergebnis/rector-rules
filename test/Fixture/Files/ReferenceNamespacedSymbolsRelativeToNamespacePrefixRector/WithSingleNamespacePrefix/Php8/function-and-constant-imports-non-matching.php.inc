<?php

namespace Example\App;

use function Other\Foo\Helper\format_name;
use const Other\Foo\Config\MAX_RETRIES;
use Psr\Http\Message\ResponseInterface;

final class ExampleService
{
    public function process(): ResponseInterface
    {
        $name = format_name('test');
        $retries = MAX_RETRIES;
    }
}

?>
