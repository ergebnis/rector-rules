<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Other\Component;

final class Kernel
{
    public function handle(Request $request, Component $other): void
    {
    }
}

?>
