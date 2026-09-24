<?php

namespace Symfony\Component\Validator;

use JsonSchema\Exception;
use Symfony\Component\Validator\Exception\CanNotResolve;

final class Validator
{
    /**
     * @throws CanNotResolve
     */
    public function validate(): void
    {
        try {
        } catch (Exception\ResourceNotFoundException $exception) {
            throw CanNotResolve::create();
        }
    }
}

?>
