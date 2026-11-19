<?php

$a = match ($value) {
    1 => 'one',
    0 => 'zero',
    -0 => 'minus zero',
};

$b = match ($status) {
    'pending' => handlePending(),
    'active' => handleActive(),
    'pending' => handlePendingAgain(),
};

?>
