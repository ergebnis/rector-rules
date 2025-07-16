<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Arrays\SortAssociativeArrayByKeyRector\WithComparisonFunctionStrcmp;

$data = [
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

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Arrays\SortAssociativeArrayByKeyRector\WithComparisonFunctionStrcmp;

$data = [
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

?>
