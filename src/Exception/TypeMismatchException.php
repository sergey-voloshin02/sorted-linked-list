<?php

declare(strict_types=1);

namespace SortedLinkedList\Exception;

use function get_debug_type;

use SortedLinkedList\ValueType;

use function sprintf;

/**
 * Exception thrown when attempting to add a value of the wrong type to a SortedLinkedList.
 *
 * A SortedLinkedList is type-homogeneous: once created for integers or strings,
 * it can only accept values of that specific type.
 */
final class TypeMismatchException extends SortedLinkedListException
{
    private function __construct(
        string $message,
        private readonly ValueType $expectedType,
        private readonly string $actualType,
    ) {
        parent::__construct($message);
    }

    /**
     * Creates an exception based on the expected ValueType.
     */
    public static function forExpectedType(ValueType $expectedType, mixed $actualValue): self
    {
        $actualType = get_debug_type($actualValue);

        return new self(
            sprintf(
                'Type mismatch: expected %s, got %s. This list only accepts %s values.',
                $expectedType->typeName(),
                $actualType,
                $expectedType === ValueType::Integer ? 'integer' : 'string',
            ),
            $expectedType,
            $actualType,
        );
    }

    /**
     * Creates an exception for when an integer was expected.
     */
    public static function expectedInteger(mixed $actualValue): self
    {
        return self::forExpectedType(ValueType::Integer, $actualValue);
    }

    /**
     * Creates an exception for when a string was expected.
     */
    public static function expectedString(mixed $actualValue): self
    {
        return self::forExpectedType(ValueType::String, $actualValue);
    }

    /**
     * Creates an exception for incompatible list types during merge.
     */
    public static function incompatibleListTypes(ValueType $thisType, ValueType $otherType): self
    {
        return new self(
            sprintf(
                'Cannot merge lists: expected %s list, got %s list.',
                $thisType->typeName(),
                $otherType->typeName(),
            ),
            $thisType,
            $otherType->typeName(),
        );
    }

    public function getExpectedType(): ValueType
    {
        return $this->expectedType;
    }

    public function getActualType(): string
    {
        return $this->actualType;
    }
}
