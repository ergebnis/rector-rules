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
    'QuZ' => 2,
    'Quux' => 4,
    'Quz' => 1,
    'quux' => 5,
    'quz' => 3,
};

?>
