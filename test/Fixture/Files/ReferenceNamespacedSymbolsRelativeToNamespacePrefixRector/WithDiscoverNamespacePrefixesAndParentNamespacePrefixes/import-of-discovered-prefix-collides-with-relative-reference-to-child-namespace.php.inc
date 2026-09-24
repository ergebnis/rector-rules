<?php

namespace Symfony\Component\Normalizer;

use JsonSchema\Exception\ResourceNotFoundException;
use JsonSchema\Exception\UriResolverException;

final class Normalizer
{
    public function normalize(): void
    {
        try {
        } catch (UriResolverException $exception) {
            throw Exception\SchemaUriCouldNotBeResolved::create();
        } catch (ResourceNotFoundException $exception) {
            throw Exception\SchemaUriCouldNotBeRead::create();
        }
    }
}

?>
