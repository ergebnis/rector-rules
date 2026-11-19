<?php

namespace App\Http;

use App\Controller as Foo;

if (!\class_exists('App\Http\Controller')) {
    final class Controller
    {
        public function dashboard(): Foo\DashboardController
        {
        }
    }
}

?>
