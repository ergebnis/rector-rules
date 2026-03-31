<?php

$a = match ($status) {
    'pending' => handlePending(),
    'active' => handleActive(),
    'closed' => handleClosed(),
};

$b = match ($code) {
    404 => 'Not Found',
    200 => 'OK',
    500 => 'Server Error',
};

?>
-----
<?php

$a = match ($status) {
    'active' => handleActive(),
    'closed' => handleClosed(),
    'pending' => handlePending(),
};

$b = match ($code) {
    200 => 'OK',
    404 => 'Not Found',
    500 => 'Server Error',
};

?>
