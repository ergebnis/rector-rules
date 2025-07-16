<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Arrays\SortAssociativeArrayByKeyRector\WithDirectionAsc;

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

namespace Ergebnis\Rector\Rules\Test\Fixture\Arrays\SortAssociativeArrayByKeyRector\WithDirectionAsc;

class Example
{
    public function data(): array
    {
        return [
            'bar' => [
                'QuZ' => 'qux',
                'Quux' => 'quuz',
                'Quz' => 'qux',
                'Quz10' => 'qux',
                'Quz2' => 'qux',
                'quux' => 'quuz',
                'quz' => 'qux',
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
