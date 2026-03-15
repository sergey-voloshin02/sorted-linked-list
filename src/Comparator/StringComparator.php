<?php

declare(strict_types=1);

namespace SortedLinkedList\Comparator;

use function assert;
use function is_string;

use Override;

use function strcmp;

/**
 * Default comparator for string values.
 *
 * Sorts strings in ascending lexicographic order using binary comparison.
 * For locale-aware or case-insensitive comparison, use a custom comparator.
 *
 * @implements ComparatorInterface<string>
 */
final readonly class StringComparator implements ComparatorInterface
{
    /**
     * @param string $a
     * @param string $b
     */
    #[Override]
    public function compare(int|string $a, int|string $b): int
    {
        assert(is_string($a) && is_string($b));

        return strcmp($a, $b);
    }
}
