<?php

namespace App\Controller\User;

/**
 * @template T
 *
 * @phpstan-type Row array{id: int}
 * @phpstan-import-type Filter from \App\Controller\Query
 * @phpstan-import-type Sort from \App\Controller\Query as QuerySort
 *
 * @method TItem first<TItem>(TItem $item)
 */
final class ListAction
{
    /**
     * @param FindAction $findAction
     * @param list<int> $ids
     * @param array-key $key
     * @param T $value
     * @param self|static $other
     * @param int<0, max> $count
     * @param Row $row
     * @param Filter $filter
     * @param QuerySort $querySort
     * @param Sort $sort
     *
     * @return ShowAction
     */
    public function __invoke(
        \App\Controller\DashboardAction $dashboard,
        $findAction,
        array $ids,
        $key,
        $value,
        $other,
        $count,
        $row,
        $filter,
        $querySort,
        $sort
    ) {
    }

    /**
     * @template TKey of array-key
     *
     * @param TKey $key
     *
     * @return non-empty-string
     */
    public function key($key): string
    {
        return 'key';
    }

    /**
     * @return noreturn
     */
    public function fail()
    {
        throw new \RuntimeException();
    }
}

?>
-----
<?php

namespace App\Controller\User;

use App\Controller;
/**
 * @template T
 *
 * @phpstan-type Row array{id: int}
 * @phpstan-import-type Filter from Controller\Query
 * @phpstan-import-type Sort from Controller\Query as QuerySort
 *
 * @method TItem first<TItem>(TItem $item)
 */
final class ListAction
{
    /**
     * @param Controller\User\FindAction $findAction
     * @param list<int> $ids
     * @param array-key $key
     * @param T $value
     * @param self|static $other
     * @param int<0, max> $count
     * @param Row $row
     * @param Filter $filter
     * @param QuerySort $querySort
     * @param Controller\User\Sort $sort
     *
     * @return Controller\User\ShowAction
     */
    public function __invoke(
        Controller\DashboardAction $dashboard,
        $findAction,
        array $ids,
        $key,
        $value,
        $other,
        $count,
        $row,
        $filter,
        $querySort,
        $sort
    ) {
    }

    /**
     * @template TKey of array-key
     *
     * @param TKey $key
     *
     * @return non-empty-string
     */
    public function key($key): string
    {
        return 'key';
    }

    /**
     * @return noreturn
     */
    public function fail()
    {
        throw new \RuntimeException();
    }
}

?>
