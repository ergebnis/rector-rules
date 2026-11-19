<?php

namespace App\Http;

use function App\Helper\render as view;

if (!\function_exists('App\Http\render')) {
    function render(): string
    {
        return view();
    }
}

?>
