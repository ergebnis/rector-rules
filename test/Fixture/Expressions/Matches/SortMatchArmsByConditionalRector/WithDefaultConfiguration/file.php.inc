<?php

use Namespaced\Bicycle;

// String conditions — should sort ascending
$a = match ($status) {
    'pending' => handlePending(),
    'active' => handleActive(),
    'closed' => handleClosed(),
};

// Integer conditions — should sort ascending
$b = match ($code) {
    404 => 'Not Found',
    200 => 'OK',
    500 => 'Server Error',
};

// ::class conditions — should sort ascending
$c = match (true) {
    Zebra::class => handleZebra(),
    Apple::class => handleApple(),
    Mango::class => handleMango(),
};

// Namespaced ::class conditions — should sort ascending
$d = match (true) {
    Namespaced\Zebra::class => handleZebra(),
    Namespaced\Apple::class => handleApple(),
    Namespaced\Mango::class => handleMango(),
    Namespaced\Bicycle::class => handleBicycle(),
};

// Imported ::class conditions — should sort ascending
$e = match (true) {
    Zebra::class => handleZebra(),
    Bicycle::class => handleBicycle(),
    Apple::class => handleApple(),
};

// default kept last — should sort non-default string arms ascending, default stays at end
$f = match ($status) {
    'pending' => handlePending(),
    default => handleUnknown(),
    'active' => handleActive(),
};

// default kept last — should sort non-default int arms ascending, default stays at end
$g = match ($code) {
    404 => 'Not Found',
    default => 'Unknown',
    200 => 'OK',
    500 => 'Server Error',
};

// Multi-condition arm — should not be modified
$h = match ($status) {
    'active', 'pending' => handleOpen(),
    'closed' => handleClosed(),
};

// Mixed types — should not be modified
$i = match ($value) {
    'foo' => 1,
    42 => 2,
};

// Non-sortable expression — should not be modified
$j = match (true) {
    $x > 10 => 'big',
    $x < 0 => 'negative',
};

// Already sorted — should not change
$k = match ($status) {
    'active' => handleActive(),
    'closed' => handleClosed(),
    'pending' => handlePending(),
};

// Only default — should not change
$l = match ($status) {
    default => handleUnknown(),
};

?>
-----
<?php

use Namespaced\Bicycle;

// String conditions — should sort ascending
$a = match ($status) {
    'active' => handleActive(),
    'closed' => handleClosed(),
    'pending' => handlePending(),
};

// Integer conditions — should sort ascending
$b = match ($code) {
    200 => 'OK',
    404 => 'Not Found',
    500 => 'Server Error',
};

// ::class conditions — should sort ascending
$c = match (true) {
    Apple::class => handleApple(),
    Mango::class => handleMango(),
    Zebra::class => handleZebra(),
};

// Namespaced ::class conditions — should sort ascending
$d = match (true) {
    Namespaced\Apple::class => handleApple(),
    Namespaced\Bicycle::class => handleBicycle(),
    Namespaced\Mango::class => handleMango(),
    Namespaced\Zebra::class => handleZebra(),
};

// Imported ::class conditions — should sort ascending
$e = match (true) {
    Apple::class => handleApple(),
    Bicycle::class => handleBicycle(),
    Zebra::class => handleZebra(),
};

// default kept last — should sort non-default string arms ascending, default stays at end
$f = match ($status) {
    'active' => handleActive(),
    'pending' => handlePending(),
    default => handleUnknown(),
};

// default kept last — should sort non-default int arms ascending, default stays at end
$g = match ($code) {
    200 => 'OK',
    404 => 'Not Found',
    500 => 'Server Error',
    default => 'Unknown',
};

// Multi-condition arm — should not be modified
$h = match ($status) {
    'active', 'pending' => handleOpen(),
    'closed' => handleClosed(),
};

// Mixed types — should not be modified
$i = match ($value) {
    'foo' => 1,
    42 => 2,
};

// Non-sortable expression — should not be modified
$j = match (true) {
    $x > 10 => 'big',
    $x < 0 => 'negative',
};

// Already sorted — should not change
$k = match ($status) {
    'active' => handleActive(),
    'closed' => handleClosed(),
    'pending' => handlePending(),
};

// Only default — should not change
$l = match ($status) {
    default => handleUnknown(),
};

?>
