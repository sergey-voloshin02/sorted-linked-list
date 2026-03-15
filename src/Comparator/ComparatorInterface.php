<?php

declare(strict_types=1);

namespace SortedLinkedList\Comparator;

/**
 * Strategy interface for comparing values in a SortedLinkedList.
 *
 * Implementations define the ordering behavior (ascending, descending, custom).
 * The compare method follows the standard spaceship operator semantics.
 *
 * @template T of int|string
 */
interface ComparatorInterface
{
    /**
     * Compares two values and returns their relative order.
     *
     * @param T $a The first value to compare
     * @param T $b The second value to compare
     *
     * @return int Negative if $a < $b, zero if $a == $b, positive if $a > $b
     */
    public function compare(int|string $a, int|string $b): int;
}
