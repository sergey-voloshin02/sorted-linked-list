<?php

declare(strict_types=1);

namespace SortedLinkedList\Comparator;

use function assert;
use function is_int;

use Override;

/**
 * Default comparator for integer values.
 *
 * Sorts integers in ascending order using natural numeric comparison.
 *
 * @implements ComparatorInterface<int>
 */
final readonly class IntegerComparator implements ComparatorInterface
{
    /**
     * @param int $a
     * @param int $b
     */
    #[Override]
    public function compare(int|string $a, int|string $b): int
    {
        assert(is_int($a) && is_int($b));

        return $a <=> $b;
    }
}
