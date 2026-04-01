<?php

$a = match ($status) {
    'Quz' => 1,
    'QuZ' => 2,
    'quz' => 3,
    'Quux' => 4,
    'quux' => 5,
};

?>
-----
<?php

$a = match ($status) {
    'Quux' => 4,
    'quux' => 5,
    'Quz' => 1,
    'QuZ' => 2,
    'quz' => 3,
};

?>
