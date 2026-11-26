<?php

namespace App\Service;

use App\Domain\User;

final class Finder
{
    /**
     * @return list<string>
     */
    public function names(string ...$names): array
    {
        $users = \array_map(static function (string $name): User {
            return new User($name);
        }, $names);

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
     * @return list<string>
     */
    public function names(string ...$names): array
    {
        $users = \array_map(static function (string $name): Domain\User {
            return new Domain\User($name);
        }, $names);

        return \array_map(function (Domain\User $user) {
            return $user->name();
        }, $users);
    }
}

?>
