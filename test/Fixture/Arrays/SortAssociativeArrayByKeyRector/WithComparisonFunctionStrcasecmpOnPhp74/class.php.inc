<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Arrays\SortAssociativeArrayByKeyRector\WithComparisonFunctionStrcasecmpOnPhp74;

class Example
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
                'Quz10' => 'qux',
                'Quz2' => 'qux',
                'Quz' => 'qux',
                'QuZ' => 'qux',
                'quz' => 'qux',
                'Quux' => 'quuz',
                'quux' => 'quuz',
            ],
        ];
    }
}

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Arrays\SortAssociativeArrayByKeyRector\WithComparisonFunctionStrcasecmpOnPhp74;

class Example
{
    public function data(): array
    {
        return [
            'bar' => [
                'quux' => 'quuz',
                'Quux' => 'quuz',
                'Quz' => 'qux',
                'QuZ' => 'qux',
                'quz' => 'qux',
                'Quz10' => 'qux',
                'Quz2' => 'qux',
            ],
            'foo' => [
                'foo',
                'bar',
                'baz',
            ],
        ];
    }
}

?>
