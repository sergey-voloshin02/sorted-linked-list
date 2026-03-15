<?php

declare(strict_types=1);

namespace SortedLinkedList;

use function is_int;
use function is_string;

/**
 * Represents the type of values that can be stored in a SortedLinkedList.
 *
 * A SortedLinkedList can only hold values of a single type - either integers or strings,
 * but never both. This enum is used to enforce and track that type constraint.
 */
enum ValueType: string
{
    case Integer = 'integer';
    case String = 'string';

    /**
     * Determines the ValueType from a given value.
     */
    public static function fromValue(int|string $value): self
    {
        return match (true) {
            is_int($value) => self::Integer,
            is_string($value) => self::String,
        };
    }

    /**
     * Returns the PHP type name for error messages.
     */
    public function typeName(): string
    {
        return match ($this) {
            self::Integer => 'int',
            self::String => 'string',
        };
    }
}
