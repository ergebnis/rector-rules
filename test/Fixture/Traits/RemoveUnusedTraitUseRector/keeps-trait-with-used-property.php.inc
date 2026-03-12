<?php

final class KeepsTraitWithUsedProperty
{
    use ExampleTraitWithProperty;

    public function doSomething(): string
    {
        return $this->traitProperty;
    }
}

?>
