<?php

final class RemovesOneTraitFromMultiUse
{
    use ExampleTraitWithProtectedMethod, ExampleTraitWithProperty;

    public function doSomething(): string
    {
        return $this->traitProperty;
    }
}

?>
-----
<?php

final class RemovesOneTraitFromMultiUse
{
    use ExampleTraitWithProperty;

    public function doSomething(): string
    {
        return $this->traitProperty;
    }
}

?>
