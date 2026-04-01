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
    'foo' => [
        'foo',
        'bar',
        'baz',
    ],
    'bar' => [
        'quz' => 'qux',
        'quux' => 'quuz',
        'Quz2' => 'qux',
        'Quz10' => 'qux',
        'Quz' => 'qux',
        'Quux' => 'quuz',
        'QuZ' => 'qux',
    ],
];

?>
