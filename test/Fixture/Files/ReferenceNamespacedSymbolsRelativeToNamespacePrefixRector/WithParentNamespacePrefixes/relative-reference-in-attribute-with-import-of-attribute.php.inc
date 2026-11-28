<?php

namespace Symfony\Component\Example\Test\EndToEnd;

use Symfony\Component\Example\Attribute\MaximumDuration;
use Symfony\Component\Example\Test;

final class SleeperTest
{
    #[MaximumDuration(Test\Fixture\Sleeper::MILLISECONDS)]
    public function testSleeps(): void
    {
        $sleeper = Test\Fixture\Sleeper::fromMilliseconds(100);
    }
}

?>
-----
<?php

namespace Symfony\Component\Example\Test\EndToEnd;

use Symfony\Component\Example;

final class SleeperTest
{
    #[Example\Attribute\MaximumDuration(Example\Test\Fixture\Sleeper::MILLISECONDS)]
    public function testSleeps(): void
    {
        $sleeper = Example\Test\Fixture\Sleeper::fromMilliseconds(100);
    }
}

?>
