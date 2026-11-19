<?php

namespace App;

use Foo\Bar\Baz;

final class Kernel
{
    public function handle(Baz $baz)
    {
        $value = \Foo\Bar\SOME_CONSTANT;
    }
}

?>
-----
<?php

namespace App;

use Foo\Bar;

final class Kernel
{
    public function handle(Bar\Baz $baz)
    {
        $value = Bar\SOME_CONSTANT;
    }
}

?>
