<?php

namespace App;

use Symfony\Component\String\UnicodeString;

final class Kernel
{
    public function handle(): UnicodeString
    {
        return new \Symfony\Component\String\ByteString('hello');
    }
}

?>
