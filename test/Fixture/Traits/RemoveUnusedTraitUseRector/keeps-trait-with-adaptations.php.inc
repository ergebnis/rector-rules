<?php

final class KeepsTraitWithAdaptations
{
    use ExampleTraitWithProtectedMethod {
        protectedMethod as private renamedMethod;
    }
}

?>
