<?php

final class RemovesTraitWithUnusedProperty
{
    use ExampleTraitWithProperty;
}

?>
-----
<?php

final class RemovesTraitWithUnusedProperty
{
}

?>
