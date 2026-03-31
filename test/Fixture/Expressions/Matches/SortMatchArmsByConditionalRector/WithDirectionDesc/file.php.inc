<?php

$a = match ($status) {
    'active' => handleActive(),
    'pending' => handlePending(),
    'closed' => handleClosed(),
};

$b = match ($code) {
    200 => 'OK',
    404 => 'Not Found',
    500 => 'Server Error',
};

?>
-----
<?php

$a = match ($status) {
    'pending' => handlePending(),
    'closed' => handleClosed(),
    'active' => handleActive(),
};

$b = match ($code) {
    500 => 'Server Error',
    404 => 'Not Found',
    200 => 'OK',
};

?>
