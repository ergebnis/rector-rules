<?php

use Faker\Generator;

function name(?Generator $faker): ?string
{
    return $faker?->name;
}

?>
-----
<?php

use Faker\Generator;

function name(?Generator $faker): ?string
{
    return $faker?->name();
}

?>
