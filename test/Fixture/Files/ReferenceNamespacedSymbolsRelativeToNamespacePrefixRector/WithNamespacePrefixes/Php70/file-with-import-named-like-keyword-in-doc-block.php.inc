<?php

namespace App\Service;

use App\Controller\Http\Resource;

final class Factory
{
    /**
     * @return Resource
     */
    public function create()
    {
        return new Resource();
    }
}

?>
-----
<?php

namespace App\Service;

use App\Controller;

final class Factory
{
    /**
     * @return Controller\Http\Resource
     */
    public function create()
    {
        return new Controller\Http\Resource();
    }
}

?>
