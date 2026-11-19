<?php

namespace App\Http;

use const App\Config\TIMEOUT as LIMIT;

const TIMEOUT = 30;

function wait(): int
{
    return LIMIT;
}

?>
