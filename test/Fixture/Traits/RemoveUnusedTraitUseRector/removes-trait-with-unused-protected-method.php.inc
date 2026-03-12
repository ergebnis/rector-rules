<?php

final class RemovesTraitWithUnusedProtectedMethod
{
    use ExampleTraitWithProtectedMethod;
}

?>
-----
<?php

final class RemovesTraitWithUnusedProtectedMethod
{
}

?>
