<?php

namespace Symfony\Component\HttpFoundation\Session;

use Symfony\Component\HttpFoundation;

final class SessionFactory
{
    /**
     * @param HttpFoundation\Session\Storage\SessionStorageInterface $storage
     */
    public function create(
        HttpFoundation\Request $request,
        HttpFoundation\Session\Storage\SessionStorageInterface $storage
    ): HttpFoundation\Session\Session {
        return new HttpFoundation\Session\Session($storage);
    }
}

?>
-----
<?php

namespace Symfony\Component\HttpFoundation\Session;

use Symfony\Component\HttpFoundation;

final class SessionFactory
{
    /**
     * @param Storage\SessionStorageInterface $storage
     */
    public function create(
        HttpFoundation\Request $request,
        Storage\SessionStorageInterface $storage
    ): Session {
        return new Session($storage);
    }
}

?>
