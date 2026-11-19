<?php

namespace App;

use Safe\Json\JsonException;

final class Kernel
{
    public function handle(): string
    {
        try {
            return \Safe\Json\json_encode([]);
        } catch (JsonException $exception) {
            return '';
        }
    }
}

?>
-----
<?php

namespace App;

use Safe\Json;

final class Kernel
{
    public function handle(): string
    {
        try {
            return Json\json_encode([]);
        } catch (Json\JsonException $exception) {
            return '';
        }
    }
}

?>
