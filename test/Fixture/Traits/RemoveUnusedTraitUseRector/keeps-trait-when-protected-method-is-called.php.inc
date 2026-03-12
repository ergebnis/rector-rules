<?php

final class KeepsTraitWhenProtectedMethodIsCalled
{
    use ExampleTraitWithProtectedMethod;

    public function doSomething(): void
    {
        $this->protectedMethod();
    }
}

?>
