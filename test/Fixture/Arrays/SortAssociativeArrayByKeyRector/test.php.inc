<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Arrays\SortAssociativeArrayByKeyRector;

use PHPUnit\Framework;

class ExampleTest extends Framework\TestCase
{
    public function data(): array
    {
        return [
            'foo' => [
                'foo',
                'bar',
                'baz',
            ],
            'bar' => [
                'quz' => 'qux',
                'quux' => 'quuz',
            ],
        ];
    }
}

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Arrays\SortAssociativeArrayByKeyRector;

use PHPUnit\Framework;

class ExampleTest extends Framework\TestCase
{
    public function data(): array
    {
        return [
            'foo' => [
                'foo',
                'bar',
                'baz',
            ],
            'bar' => [
                'quz' => 'qux',
                'quux' => 'quuz',
            ],
        ];
    }
}

?>
