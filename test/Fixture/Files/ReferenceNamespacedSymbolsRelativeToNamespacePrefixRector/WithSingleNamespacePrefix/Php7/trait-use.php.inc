<?php

namespace Example\App;

use Example\Core\Concern\HasTimestamps;
use Example\Core\Concern\SoftDeletes;
use Psr\Log\LoggerAwareTrait;

final class ExampleEntity
{
    use HasTimestamps;
    use SoftDeletes;
    use LoggerAwareTrait;
}

?>
-----
<?php

namespace Example\App;

use Example\Core;
use Psr\Log\LoggerAwareTrait;

final class ExampleEntity
{
    use Core\Concern\HasTimestamps;
    use Core\Concern\SoftDeletes;
    use LoggerAwareTrait;
}

?>
