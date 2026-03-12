<?php

namespace Example\App;

use Example\Core\Exception\NotFoundException;
use Example\Core\Exception\ValidationException;
use Example\Core\Contract\Arrayable;
use Psr\Http\Message\ResponseInterface;

final class ExampleService
{
    public function process($entity): ResponseInterface
    {
        if ($entity instanceof Arrayable) {
            $entity = $entity->toArray();
        }

        try {
            return $this->handle($entity);
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (ValidationException $exception) {
            throw $exception;
        }
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;
use Psr\Http\Message\ResponseInterface;

final class ExampleService
{
    public function process($entity): ResponseInterface
    {
        if ($entity instanceof Core\Contract\Arrayable) {
            $entity = $entity->toArray();
        }

        try {
            return $this->handle($entity);
        } catch (Core\Exception\NotFoundException $exception) {
            throw $exception;
        } catch (Core\Exception\ValidationException $exception) {
            throw $exception;
        }
    }
}

?>
