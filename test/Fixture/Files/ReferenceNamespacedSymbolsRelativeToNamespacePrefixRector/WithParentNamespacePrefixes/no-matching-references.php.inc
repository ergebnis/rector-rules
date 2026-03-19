<?php

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final class Kernel
{
    public function handle(LoggerInterface $logger): ResponseInterface
    {
    }
}

?>
