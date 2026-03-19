<?php

namespace App\Service;

use Psr\Http\Message\ResponseInterface;
use Other\Foo\Repository\DepartmentRepository;

final class NoMatchService
{
    public function handle(DepartmentRepository $repository): ResponseInterface
    {
    }
}

?>
