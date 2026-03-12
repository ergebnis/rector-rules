<?php

final class RemovesTraitWithUnusedPrivateMethod
{
    use ExampleTraitWithPrivateMethod;
}

?>
-----
<?php

final class RemovesTraitWithUnusedPrivateMethod
{
}

?>
