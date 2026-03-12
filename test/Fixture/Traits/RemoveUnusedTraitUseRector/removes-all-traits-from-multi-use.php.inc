<?php

final class RemovesAllTraitsFromMultiUse
{
    use ExampleTraitWithProtectedMethod, ExampleTraitWithSecondProtectedMethod;
}

?>
-----
<?php

final class RemovesAllTraitsFromMultiUse
{
}

?>
