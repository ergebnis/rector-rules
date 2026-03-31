<?php

namespace App;

final class Kernel
{
    public function handle()
    {
        \Safe\unlink('/tmp/file.txt');
        \Safe\file_get_contents('/tmp/file.txt');
    }
}

?>
