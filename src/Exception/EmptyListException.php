<?php

declare(strict_types=1);

namespace SortedLinkedList\Exception;

/**
 * Exception thrown when an operation requires a non-empty list.
 *
 * Operations like firstOrFail() or removeFirst() cannot be performed
 * on an empty list.
 */
final class EmptyListException extends SortedLinkedListException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Creates an exception for when first() is called on an empty list.
     */
    public static function cannotGetFirst(): self
    {
        return new self('Cannot get first element: the list is empty.');
    }

    /**
     * Creates an exception for when last() is called on an empty list.
     */
    public static function cannotGetLast(): self
    {
        return new self('Cannot get last element: the list is empty.');
    }

    /**
     * Creates an exception for when remove operations are called on an empty list.
     */
    public static function cannotRemove(): self
    {
        return new self('Cannot remove element: the list is empty.');
    }
}
