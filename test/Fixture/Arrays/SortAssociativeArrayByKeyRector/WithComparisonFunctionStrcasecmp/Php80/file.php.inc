<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Arrays\SortAssociativeArrayByKeyRector\WithComparisonFunctionStrcasecmp\Php80;

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

namespace Ergebnis\Rector\Rules\Test\Fixture\Arrays\SortAssociativeArrayByKeyRector\WithComparisonFunctionStrcasecmp\Php80;

$data = [
    'bar' => [
        'Quux' => 'quuz',
        'quux' => 'quuz',
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

?>
