<?php

namespace App\Service;

use App\Domain\User;

final class Finder
{
    /**
     * @param list<User> $users
     *
     * @return list<string>
     */
    public function names(array $users): array
    {
        return \array_map(function ($user) {
            return $user->name();
        }, $users);
    }
}

?>
-----
<?php

namespace App\Service;

use App\Domain;

final class Finder
{
    /**
     * @param list<Domain\User> $users
     *
     * @return list<string>
     */
    public function names(array $users): array
    {
        return \array_map(function (Domain\User $user) {
            return $user->name();
        }, $users);
    }
}

?>
