<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Other\Foo\Repository\DepartmentRepository;

final class NoMatchController
{
    public function handle(DepartmentRepository $repository): ResponseInterface
    {
    }
}

?>
