<?php

declare(strict_types=1);

namespace SortedLinkedList;

/**
 * Internal immutable node for the linked list.
 *
 * @template T of int|string
 *
 * @internal this class is not part of the public API and may change without notice
 */
final readonly class Node
{
    /**
     * @param T $value The value stored in this node
     * @param self<T>|null $next Reference to the next node, or null if this is the last node
     */
    public function __construct(
        public int|string $value,
        public ?self $next = null,
    ) {
    }

    /**
     * Creates a new node with a different next reference.
     *
     * Since nodes are immutable, this returns a new instance.
     *
     * @param self<T>|null $next The new next node
     *
     * @return self<T> A new node with the same value but different next reference
     */
    public function withNext(?self $next): self
    {
        return new self($this->value, $next);
    }
}
