<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Arrays\SortAssociativeArrayByKeyRector\WithDefaultConfiguration\Php80;

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

$rules = [
    'ErickSkrauch/line_break_after_statements' => true,
    'PhpCsFixerCustomFixers/multiline_comment_opening_closing_alone' => true,
    'PhpCsFixerCustomFixers/no_duplicated_array_key' => [
        'ignore_expressions' => true,
    ],
    'PhpCsFixerCustomFixers/no_duplicated_imports' => true,
    'PhpCsFixerCustomFixers/php_unit_requires_constraint' => true,
    'PhpCsFixerCustomFixers/phpdoc_types_comma_spaces' => true,
    'PhpCsFixerCustomFixers/typed_class_constant' => true,
    'binary_operator_spaces' => [
        'default' => 'single_space',
        'operators' => [],
    ],
    'blank_line_between_import_groups' => false,
    'blank_lines_before_namespace' => [
        'max_line_breaks' => 2,
        'min_line_breaks' => 2,
    ],
];

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Arrays\SortAssociativeArrayByKeyRector\WithDefaultConfiguration\Php80;

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

$rules = [
    'ErickSkrauch/line_break_after_statements' => true,
    'PhpCsFixerCustomFixers/multiline_comment_opening_closing_alone' => true,
    'PhpCsFixerCustomFixers/no_duplicated_array_key' => [
        'ignore_expressions' => true,
    ],
    'PhpCsFixerCustomFixers/no_duplicated_imports' => true,
    'PhpCsFixerCustomFixers/php_unit_requires_constraint' => true,
    'PhpCsFixerCustomFixers/phpdoc_types_comma_spaces' => true,
    'PhpCsFixerCustomFixers/typed_class_constant' => true,
    'binary_operator_spaces' => [
        'default' => 'single_space',
        'operators' => [],
    ],
    'blank_line_between_import_groups' => false,
    'blank_lines_before_namespace' => [
        'max_line_breaks' => 2,
        'min_line_breaks' => 2,
    ],
];

?>
