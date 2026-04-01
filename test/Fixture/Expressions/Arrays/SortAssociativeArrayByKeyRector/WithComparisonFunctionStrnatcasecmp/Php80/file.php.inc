<?php

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

$data = [
    'bar' => [
        'Quux' => 'quuz',
        'quux' => 'quuz',
        'Quz' => 'qux',
        'QuZ' => 'qux',
        'quz' => 'qux',
        'Quz2' => 'qux',
        'Quz10' => 'qux',
    ],
    'foo' => [
        'foo',
        'bar',
        'baz',
    ],
];

?>
