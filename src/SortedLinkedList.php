<?php

declare(strict_types=1);

namespace SortedLinkedList;

use function array_values;

use Countable;
use Generator;
use InvalidArgumentException;

use function is_int;

use IteratorAggregate;
use JsonSerializable;
use Override;
use SortedLinkedList\Comparator\ComparatorInterface;
use SortedLinkedList\Comparator\IntegerComparator;
use SortedLinkedList\Comparator\ReverseComparator;
use SortedLinkedList\Comparator\StringComparator;
use SortedLinkedList\Exception\EmptyListException;
use SortedLinkedList\Exception\TypeMismatchException;

/**
 * An immutable sorted linked list that holds either integers or strings.
 *
 * This data structure maintains its elements in sorted order at all times.
 * All mutating operations return a new instance, leaving the original unchanged.
 *
 * Type Safety:
 * - A list created with `ofIntegers()` only accepts integers
 * - A list created with `ofStrings()` only accepts strings
 * - Attempting to mix types throws `TypeMismatchException`
 *
 * Example:
 *     $list = SortedLinkedList::ofIntegers()
 *         ->add(5)
 *         ->add(1)
 *         ->add(3);
 *
 *     foreach ($list as $value) {
 *         echo $value; // Outputs: 1, 3, 5
 *     }
 *
 * @template T of int|string
 *
 * @implements IteratorAggregate<int, T>
 */
final readonly class SortedLinkedList implements Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @param Node<T>|null $head The first node in the list
     * @param ValueType $valueType The type of values this list accepts
     * @param ComparatorInterface<T> $comparator The comparator for sorting
     * @param int<0, max> $count The number of elements in the list
     */
    private function __construct(
        private ?Node $head,
        private ValueType $valueType,
        private ComparatorInterface $comparator,
        private int $count,
    ) {
    }

    // ==================== Factory Methods ====================

    /**
     * Creates an empty list for integer values.
     *
     * @param ComparatorInterface<int>|null $comparator Custom comparator, defaults to ascending order
     *
     * @return self<int>
     */
    public static function ofIntegers(?ComparatorInterface $comparator = null): self
    {
        return new self(
            head: null,
            valueType: ValueType::Integer,
            comparator: $comparator ?? new IntegerComparator(),
            count: 0,
        );
    }

    /**
     * Creates an empty list for string values.
     *
     * @param ComparatorInterface<string>|null $comparator Custom comparator, defaults to ascending order
     *
     * @return self<string>
     */
    public static function ofStrings(?ComparatorInterface $comparator = null): self
    {
        return new self(
            head: null,
            valueType: ValueType::String,
            comparator: $comparator ?? new StringComparator(),
            count: 0,
        );
    }

    /**
     * Creates a list from an array of integers.
     *
     * @param array<int> $values The values to add to the list
     * @param ComparatorInterface<int>|null $comparator Custom comparator
     *
     * @return self<int>
     */
    public static function fromIntegerArray(array $values, ?ComparatorInterface $comparator = null): self
    {
        $list = self::ofIntegers($comparator);

        foreach ($values as $value) {
            $list = $list->add($value);
        }

        return $list;
    }

    /**
     * Creates a list from an array of strings.
     *
     * @param array<string> $values The values to add to the list
     * @param ComparatorInterface<string>|null $comparator Custom comparator
     *
     * @return self<string>
     */
    public static function fromStringArray(array $values, ?ComparatorInterface $comparator = null): self
    {
        $list = self::ofStrings($comparator);

        foreach ($values as $value) {
            $list = $list->add($value);
        }

        return $list;
    }

    /**
     * Creates a list from an array, auto-detecting the type from the first element.
     *
     * @param array<int>|array<string> $values The values to add to the list (must not be empty)
     * @param ComparatorInterface<int>|ComparatorInterface<string>|null $comparator Custom comparator
     *
     * @throws InvalidArgumentException If the array is empty
     * @throws TypeMismatchException If the array contains mixed types
     *
     * @return self<int>|self<string>
     */
    public static function fromArray(array $values, ?ComparatorInterface $comparator = null): self
    {
        $values = array_values($values);

        if ($values === []) {
            throw new InvalidArgumentException(
                'Cannot auto-detect type from empty array. Use ofIntegers() or ofStrings() explicitly.',
            );
        }

        $firstValue = $values[0];

        if (is_int($firstValue)) {
            /** @var array<int> $values */
            /** @var ComparatorInterface<int>|null $comparator */
            return self::fromIntegerArray($values, $comparator);
        }

        /** @var array<string> $values */
        /** @var ComparatorInterface<string>|null $comparator */
        return self::fromStringArray($values, $comparator);
    }

    // ==================== Core Operations ====================

    /**
     * Adds a value to the list in sorted order.
     *
     * @param T $value The value to add
     *
     * @throws TypeMismatchException If the value type doesn't match the list type
     *
     * @return self<T> A new list containing the added value
     */
    public function add(int|string $value): self
    {
        $this->validateType($value);

        $newNode = new Node($value);

        if ($this->head === null) {
            return new self($newNode, $this->valueType, $this->comparator, 1);
        }

        // Insert at head if new value comes before current head
        if ($this->comparator->compare($value, $this->head->value) <= 0) {
            return new self(
                new Node($value, $this->head),
                $this->valueType,
                $this->comparator,
                $this->count + 1,
            );
        }

        // Find insertion point and rebuild the list
        $newHead = $this->insertSorted($this->head, $value);

        return new self($newHead, $this->valueType, $this->comparator, $this->count + 1);
    }

    /**
     * Removes the first occurrence of a value from the list.
     *
     * @param T $value The value to remove
     *
     * @return self<T> A new list without the value (unchanged if not found)
     */
    public function remove(int|string $value): self
    {
        if ($this->head === null) {
            return $this;
        }

        // Remove head if it matches
        if ($this->head->value === $value) {
            /** @var int<0, max> $newCount */
            $newCount = $this->count - 1;

            return new self(
                $this->head->next,
                $this->valueType,
                $this->comparator,
                $newCount,
            );
        }

        // Search and remove from list
        $newHead = $this->removeFromList($this->head, $value);

        if ($newHead === $this->head) {
            // Value not found, return same instance
            return $this;
        }

        /** @var int<0, max> $newCount */
        $newCount = $this->count - 1;

        return new self($newHead, $this->valueType, $this->comparator, $newCount);
    }

    /**
     * Removes the first element from the list.
     *
     * @throws EmptyListException If the list is empty
     *
     * @return self<T> A new list without the first element
     */
    public function removeFirst(): self
    {
        if ($this->head === null) {
            throw EmptyListException::cannotRemove();
        }

        /** @var int<0, max> $newCount */
        $newCount = $this->count - 1;

        return new self(
            $this->head->next,
            $this->valueType,
            $this->comparator,
            $newCount,
        );
    }

    /**
     * Removes the last element from the list.
     *
     * @throws EmptyListException If the list is empty
     *
     * @return self<T> A new list without the last element
     */
    public function removeLast(): self
    {
        if ($this->head === null) {
            throw EmptyListException::cannotRemove();
        }

        if ($this->head->next === null) {
            return new self(null, $this->valueType, $this->comparator, 0);
        }

        $newHead = $this->removeLastNode($this->head);

        /** @var int<0, max> $newCount */
        $newCount = $this->count - 1;

        return new self($newHead, $this->valueType, $this->comparator, $newCount);
    }

    /**
     * Merges another sorted list into this one.
     *
     * @param self<T> $other The list to merge
     *
     * @throws TypeMismatchException If the lists have different value types
     *
     * @return self<T> A new list containing all elements from both lists
     */
    public function merge(self $other): self
    {
        if ($this->valueType !== $other->valueType) {
            throw TypeMismatchException::incompatibleListTypes($this->valueType, $other->valueType);
        }

        $result = $this;

        foreach ($other as $value) {
            $result = $result->add($value);
        }

        return $result;
    }

    // ==================== Query Methods ====================

    /**
     * Returns the first element in the list.
     *
     * @return T|null The first element, or null if the list is empty
     */
    public function first(): int|string|null
    {
        return $this->head?->value;
    }

    /**
     * Returns the first element or throws if the list is empty.
     *
     * @throws EmptyListException If the list is empty
     *
     * @return T The first element
     */
    public function firstOrFail(): int|string
    {
        if ($this->head === null) {
            throw EmptyListException::cannotGetFirst();
        }

        return $this->head->value;
    }

    /**
     * Returns the last element in the list.
     *
     * @return T|null The last element, or null if the list is empty
     */
    public function last(): int|string|null
    {
        if ($this->head === null) {
            return null;
        }

        $current = $this->head;

        while ($current->next !== null) {
            $current = $current->next;
        }

        return $current->value;
    }

    /**
     * Returns the last element or throws if the list is empty.
     *
     * @throws EmptyListException If the list is empty
     *
     * @return T The last element
     */
    public function lastOrFail(): int|string
    {
        $last = $this->last();

        if ($last === null) {
            throw EmptyListException::cannotGetLast();
        }

        return $last;
    }

    /**
     * Returns the element at the given index.
     *
     * @param int<0, max> $index The zero-based index
     *
     * @return T|null The element at the index, or null if out of bounds
     */
    public function get(int $index): int|string|null
    {
        if ($index < 0 || $index >= $this->count) {
            return null;
        }

        $current = $this->head;
        $currentIndex = 0;

        while ($current !== null && $currentIndex < $index) {
            $current = $current->next;
            ++$currentIndex;
        }

        return $current?->value;
    }

    /**
     * Checks if the list contains a value.
     *
     * @param T $value The value to search for
     */
    public function contains(int|string $value): bool
    {
        return $this->indexOf($value) !== null;
    }

    /**
     * Returns the index of the first occurrence of a value.
     *
     * @param T $value The value to search for
     *
     * @return int<0, max>|null The index, or null if not found
     */
    public function indexOf(int|string $value): ?int
    {
        $current = $this->head;
        $index = 0;

        while ($current !== null) {
            if ($current->value === $value) {
                return $index;
            }

            // Early termination: if current value is greater than search value,
            // the value cannot exist in the remaining sorted list
            if ($this->comparator->compare($current->value, $value) > 0) {
                return null;
            }

            $current = $current->next;
            ++$index;
        }

        return null;
    }

    /**
     * Checks if the list is empty.
     */
    public function isEmpty(): bool
    {
        return $this->head === null;
    }

    /**
     * Returns the number of elements in the list.
     *
     * @return int<0, max>
     */
    #[Override]
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Returns the type of values this list accepts.
     */
    public function getValueType(): ValueType
    {
        return $this->valueType;
    }

    // ==================== Transformation ====================

    /**
     * Converts the list to an array.
     *
     * @return array<int, T>
     */
    public function toArray(): array
    {
        $result = [];
        $current = $this->head;

        while ($current !== null) {
            $result[] = $current->value;
            $current = $current->next;
        }

        return $result;
    }

    /**
     * Returns a new list with reversed sort order.
     *
     * @return self<T>
     */
    public function reversed(): self
    {
        /**
         * @var ComparatorInterface<T> $reversedComparator
         *
         * @phpstan-ignore varTag.nativeType
         */
        $reversedComparator = new ReverseComparator($this->comparator);

        /** @var self<T> $result */
        $result = new self(null, $this->valueType, $reversedComparator, 0);

        foreach ($this as $value) {
            $result = $result->add($value);
        }

        return $result;
    }

    /**
     * Filters the list using a predicate.
     *
     * @param callable(T): bool $predicate
     *
     * @return self<T>
     */
    public function filter(callable $predicate): self
    {
        $result = new self(null, $this->valueType, $this->comparator, 0);

        foreach ($this as $value) {
            if ($predicate($value)) {
                $result = $result->add($value);
            }
        }

        return $result;
    }

    /**
     * Applies a callback to each element and returns results as an array.
     *
     * Note: This returns an array, not a SortedLinkedList, because the callback
     * may transform values to different types.
     *
     * @template U
     *
     * @param callable(T): U $callback
     *
     * @return array<int, U>
     */
    public function map(callable $callback): array
    {
        $result = [];

        foreach ($this as $value) {
            $result[] = $callback($value);
        }

        return $result;
    }

    /**
     * Reduces the list to a single value.
     *
     * @template U
     *
     * @param callable(U, T): U $callback
     * @param U $initial
     *
     * @return U
     */
    public function reduce(callable $callback, mixed $initial): mixed
    {
        $accumulator = $initial;

        foreach ($this as $value) {
            $accumulator = $callback($accumulator, $value);
        }

        return $accumulator;
    }

    // ==================== Interface Implementations ====================

    /**
     * @return Generator<int, T>
     */
    #[Override]
    public function getIterator(): Generator
    {
        $current = $this->head;
        $index = 0;

        while ($current !== null) {
            yield $index++ => $current->value;
            $current = $current->next;
        }
    }

    /**
     * @return array<int, T>
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // ==================== Private Helpers ====================

    /**
     * Validates that the value matches the list's type.
     *
     * @throws TypeMismatchException If the type doesn't match
     */
    private function validateType(int|string $value): void
    {
        $valueType = ValueType::fromValue($value);

        if ($valueType !== $this->valueType) {
            throw TypeMismatchException::forExpectedType($this->valueType, $value);
        }
    }

    /**
     * Inserts a value into the list in sorted order, returning a new head.
     *
     * @param Node<T> $node Current node
     * @param T $value Value to insert
     *
     * @return Node<T> New head node
     */
    private function insertSorted(Node $node, int|string $value): Node
    {
        // If we've reached the end or found the insertion point
        if ($node->next === null || $this->comparator->compare($value, $node->next->value) <= 0) {
            return $node->withNext(new Node($value, $node->next));
        }

        // Continue searching, rebuilding the list
        return $node->withNext($this->insertSorted($node->next, $value));
    }

    /**
     * Removes a value from the list, returning a new head.
     *
     * @param Node<T> $node Current node
     * @param T $value Value to remove
     *
     * @return Node<T> New head node (may be same as input if value not found)
     */
    private function removeFromList(Node $node, int|string $value): Node
    {
        if ($node->next === null) {
            return $node;
        }

        if ($node->next->value === $value) {
            return $node->withNext($node->next->next);
        }

        // Early termination for sorted list
        if ($this->comparator->compare($node->next->value, $value) > 0) {
            return $node;
        }

        $newNext = $this->removeFromList($node->next, $value);

        if ($newNext === $node->next) {
            return $node;
        }

        return $node->withNext($newNext);
    }

    /**
     * Removes the last node from the list.
     *
     * @param Node<T> $node Current node (must have a next node)
     *
     * @return Node<T> New head node
     */
    private function removeLastNode(Node $node): Node
    {
        if ($node->next?->next === null) {
            return $node->withNext(null);
        }

        return $node->withNext($this->removeLastNode($node->next));
    }
}
