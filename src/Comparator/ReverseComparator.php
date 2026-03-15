<?php

declare(strict_types=1);

namespace SortedLinkedList\Comparator;

use Override;

/**
 * Decorator that reverses the order of any comparator.
 *
 * Wraps another comparator and inverts its comparison result,
 * effectively changing ascending order to descending and vice versa.
 *
 * Example:
 *     $desc = new ReverseComparator(new IntegerComparator());
 *     $list = SortedLinkedList::ofIntegers($desc); // Descending order
 *
 * @template T of int|string
 *
 * @implements ComparatorInterface<T>
 */
final readonly class ReverseComparator implements ComparatorInterface
{
    /**
     * @param ComparatorInterface<T> $inner The comparator to reverse
     */
    public function __construct(
        private ComparatorInterface $inner,
    ) {
    }

    #[Override]
    public function compare(int|string $a, int|string $b): int
    {
        return -$this->inner->compare($a, $b);
    }
}
