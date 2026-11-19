<?php

$a = match ($value) {
    1 => 'one',
    -1 => 'minus one',
    0 => 'zero',
    -10 => 'minus ten',
};

?>
-----
<?php

$a = match ($value) {
    1 => 'one',
    0 => 'zero',
    -1 => 'minus one',
    -10 => 'minus ten',
};

?>
