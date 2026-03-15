<?php

declare(strict_types=1);

namespace SortedLinkedList\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SortedLinkedList\Comparator\IntegerComparator;
use SortedLinkedList\Comparator\ReverseComparator;
use SortedLinkedList\Comparator\StringComparator;
use SortedLinkedList\Exception\EmptyListException;
use SortedLinkedList\Exception\TypeMismatchException;
use SortedLinkedList\Node;
use SortedLinkedList\SortedLinkedList;
use SortedLinkedList\ValueType;

#[CoversClass(SortedLinkedList::class)]
#[CoversClass(Node::class)]
#[CoversClass(ValueType::class)]
final class SortedLinkedListTest extends TestCase
{
    // ==================== Factory Methods ====================

    #[Test]
    public function ofIntegersCreatesEmptyIntegerList(): void
    {
        $list = SortedLinkedList::ofIntegers();

        self::assertTrue($list->isEmpty());
        self::assertSame(0, $list->count());
        self::assertSame(ValueType::Integer, $list->getValueType());
    }

    #[Test]
    public function ofStringsCreatesEmptyStringList(): void
    {
        $list = SortedLinkedList::ofStrings();

        self::assertTrue($list->isEmpty());
        self::assertSame(0, $list->count());
        self::assertSame(ValueType::String, $list->getValueType());
    }

    #[Test]
    public function fromIntegerArrayCreatesListWithSortedValues(): void
    {
        $list = SortedLinkedList::fromIntegerArray([5, 2, 8, 1, 9]);

        self::assertSame([1, 2, 5, 8, 9], $list->toArray());
        self::assertSame(5, $list->count());
        self::assertSame(ValueType::Integer, $list->getValueType());
    }

    #[Test]
    public function fromStringArrayCreatesListWithSortedValues(): void
    {
        $list = SortedLinkedList::fromStringArray(['banana', 'apple', 'cherry']);

        self::assertSame(['apple', 'banana', 'cherry'], $list->toArray());
        self::assertSame(ValueType::String, $list->getValueType());
    }

    #[Test]
    public function fromArrayAutoDetectsIntegerType(): void
    {
        $list = SortedLinkedList::fromArray([5, 2, 8, 1, 9]);

        self::assertSame([1, 2, 5, 8, 9], $list->toArray());
        self::assertSame(ValueType::Integer, $list->getValueType());
    }

    #[Test]
    public function fromArrayAutoDetectsStringType(): void
    {
        $list = SortedLinkedList::fromArray(['banana', 'apple', 'cherry']);

        self::assertSame(['apple', 'banana', 'cherry'], $list->toArray());
        self::assertSame(ValueType::String, $list->getValueType());
    }

    #[Test]
    public function fromEmptyArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot auto-detect type from empty array');

        SortedLinkedList::fromArray([]);
    }

    #[Test]
    public function fromIntegerArrayWithCustomComparator(): void
    {
        $list = SortedLinkedList::fromIntegerArray(
            [1, 5, 3],
            new ReverseComparator(new IntegerComparator()),
        );

        self::assertSame([5, 3, 1], $list->toArray());
    }

    // ==================== Add Operation ====================

    #[Test]
    public function addMaintainsSortedOrder(): void
    {
        $list = SortedLinkedList::ofIntegers()
            ->add(5)
            ->add(1)
            ->add(3)
            ->add(2)
            ->add(4);

        self::assertSame([1, 2, 3, 4, 5], $list->toArray());
    }

    #[Test]
    public function addAtBeginning(): void
    {
        $list = SortedLinkedList::ofIntegers()->add(5)->add(3)->add(1);

        self::assertSame([1, 3, 5], $list->toArray());
        self::assertSame(1, $list->first());
    }

    #[Test]
    public function addAtEnd(): void
    {
        $list = SortedLinkedList::ofIntegers()->add(1)->add(3)->add(5);

        self::assertSame([1, 3, 5], $list->toArray());
        self::assertSame(5, $list->last());
    }

    #[Test]
    public function addDuplicateValues(): void
    {
        $list = SortedLinkedList::ofIntegers()
            ->add(3)
            ->add(1)
            ->add(3)
            ->add(2)
            ->add(3);

        self::assertSame([1, 2, 3, 3, 3], $list->toArray());
    }

    #[Test]
    public function addStringToIntegerListThrows(): void
    {
        $list = SortedLinkedList::ofIntegers()->add(1);

        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('expected int');

        // @phpstan-ignore argument.type (intentionally testing runtime type validation)
        $list->add('string');
    }

    #[Test]
    public function addIntegerToStringListThrows(): void
    {
        $list = SortedLinkedList::ofStrings()->add('hello');

        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('expected string');

        // @phpstan-ignore argument.type (intentionally testing runtime type validation)
        $list->add(42);
    }

    #[Test]
    public function addReturnsNewInstance(): void
    {
        $original = SortedLinkedList::ofIntegers()->add(1);
        $modified = $original->add(2);

        self::assertNotSame($original, $modified);
        self::assertSame([1], $original->toArray());
        self::assertSame([1, 2], $modified->toArray());
    }

    // ==================== Remove Operation ====================

    #[Test]
    public function removeExistingValue(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3, 4, 5]);
        $result = $list->remove(3);

        self::assertSame([1, 2, 4, 5], $result->toArray());
    }

    #[Test]
    public function removeFirstElement(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3]);
        $result = $list->remove(1);

        self::assertSame([2, 3], $result->toArray());
    }

    #[Test]
    public function removeLastElement(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3]);
        $result = $list->remove(3);

        self::assertSame([1, 2], $result->toArray());
    }

    #[Test]
    public function removeNonExistentValueReturnsUnchanged(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3]);
        $result = $list->remove(99);

        self::assertSame($list, $result);
    }

    #[Test]
    public function removeFromEmptyListReturnsUnchanged(): void
    {
        $list = SortedLinkedList::ofIntegers();
        $result = $list->remove(1);

        self::assertSame($list, $result);
    }

    #[Test]
    public function removeOnlyFirstOccurrence(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 2, 2, 3]);
        $result = $list->remove(2);

        self::assertSame([1, 2, 2, 3], $result->toArray());
    }

    #[Test]
    public function removeFirstFromList(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3]);
        $result = $list->removeFirst();

        self::assertSame([2, 3], $result->toArray());
    }

    #[Test]
    public function removeFirstFromEmptyListThrows(): void
    {
        $list = SortedLinkedList::ofIntegers();

        $this->expectException(EmptyListException::class);

        $list->removeFirst();
    }

    #[Test]
    public function removeLastFromList(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3]);
        $result = $list->removeLast();

        self::assertSame([1, 2], $result->toArray());
    }

    #[Test]
    public function removeLastFromSingleElementList(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1]);
        $result = $list->removeLast();

        self::assertTrue($result->isEmpty());
    }

    #[Test]
    public function removeLastFromEmptyListThrows(): void
    {
        $list = SortedLinkedList::ofIntegers();

        $this->expectException(EmptyListException::class);

        $list->removeLast();
    }

    // ==================== Query Methods ====================

    #[Test]
    public function firstReturnsFirstElement(): void
    {
        $list = SortedLinkedList::fromIntegerArray([5, 1, 3]);

        self::assertSame(1, $list->first());
    }

    #[Test]
    public function firstReturnsNullForEmptyList(): void
    {
        $list = SortedLinkedList::ofIntegers();

        self::assertNull($list->first());
    }

    #[Test]
    public function firstOrFailThrowsForEmptyList(): void
    {
        $list = SortedLinkedList::ofIntegers();

        $this->expectException(EmptyListException::class);

        $list->firstOrFail();
    }

    #[Test]
    public function lastReturnsLastElement(): void
    {
        $list = SortedLinkedList::fromIntegerArray([5, 1, 3]);

        self::assertSame(5, $list->last());
    }

    #[Test]
    public function lastReturnsNullForEmptyList(): void
    {
        $list = SortedLinkedList::ofIntegers();

        self::assertNull($list->last());
    }

    #[Test]
    public function lastOrFailThrowsForEmptyList(): void
    {
        $list = SortedLinkedList::ofIntegers();

        $this->expectException(EmptyListException::class);

        $list->lastOrFail();
    }

    #[Test]
    public function getReturnsElementAtIndex(): void
    {
        $list = SortedLinkedList::fromIntegerArray([10, 20, 30]);

        self::assertSame(10, $list->get(0));
        self::assertSame(20, $list->get(1));
        self::assertSame(30, $list->get(2));
    }

    #[Test]
    public function getReturnsNullForOutOfBoundsIndex(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3]);

        self::assertNull($list->get(5));
        self::assertNull($list->get(100));
    }

    #[Test]
    public function containsReturnsTrueForExistingValue(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3, 4, 5]);

        self::assertTrue($list->contains(3));
        self::assertTrue($list->contains(1));
        self::assertTrue($list->contains(5));
    }

    #[Test]
    public function containsReturnsFalseForNonExistingValue(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3, 4, 5]);

        self::assertFalse($list->contains(0));
        self::assertFalse($list->contains(6));
        self::assertFalse($list->contains(99));
    }

    #[Test]
    public function indexOfReturnsCorrectIndex(): void
    {
        $list = SortedLinkedList::fromIntegerArray([10, 20, 30, 40]);

        self::assertSame(0, $list->indexOf(10));
        self::assertSame(1, $list->indexOf(20));
        self::assertSame(3, $list->indexOf(40));
    }

    #[Test]
    public function indexOfReturnsNullForNonExistingValue(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3]);

        self::assertNull($list->indexOf(99));
    }

    #[Test]
    public function isEmptyReturnsTrueForEmptyList(): void
    {
        self::assertTrue(SortedLinkedList::ofIntegers()->isEmpty());
        self::assertTrue(SortedLinkedList::ofStrings()->isEmpty());
    }

    #[Test]
    public function isEmptyReturnsFalseForNonEmptyList(): void
    {
        self::assertFalse(SortedLinkedList::ofIntegers()->add(1)->isEmpty());
    }

    #[Test]
    public function countReturnsCorrectCount(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3, 4, 5]);

        self::assertSame(5, $list->count());
        self::assertCount(5, $list);
    }

    // ==================== Merge Operation ====================

    #[Test]
    public function mergeTwoLists(): void
    {
        $list1 = SortedLinkedList::fromIntegerArray([1, 3, 5]);
        $list2 = SortedLinkedList::fromIntegerArray([2, 4, 6]);

        $merged = $list1->merge($list2);

        self::assertSame([1, 2, 3, 4, 5, 6], $merged->toArray());
    }

    #[Test]
    public function mergeWithEmptyList(): void
    {
        $list1 = SortedLinkedList::fromIntegerArray([1, 2, 3]);
        $list2 = SortedLinkedList::ofIntegers();

        $merged = $list1->merge($list2);

        self::assertSame([1, 2, 3], $merged->toArray());
    }

    #[Test]
    public function mergeIntoEmptyList(): void
    {
        $list1 = SortedLinkedList::ofIntegers();
        $list2 = SortedLinkedList::fromIntegerArray([1, 2, 3]);

        $merged = $list1->merge($list2);

        self::assertSame([1, 2, 3], $merged->toArray());
    }

    #[Test]
    public function mergeDifferentTypesThrows(): void
    {
        $intList = SortedLinkedList::ofIntegers()->add(1);
        $stringList = SortedLinkedList::ofStrings()->add('a');

        $this->expectException(TypeMismatchException::class);

        // @phpstan-ignore argument.type (intentionally testing runtime type validation)
        $intList->merge($stringList);
    }

    // ==================== Transformation ====================

    #[Test]
    public function toArrayReturnsCorrectArray(): void
    {
        $list = SortedLinkedList::fromIntegerArray([3, 1, 2]);

        self::assertSame([1, 2, 3], $list->toArray());
    }

    #[Test]
    public function toArrayForEmptyListReturnsEmptyArray(): void
    {
        $list = SortedLinkedList::ofIntegers();

        self::assertSame([], $list->toArray());
    }

    #[Test]
    public function reversedReturnsDescendingOrder(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3, 4, 5]);
        $reversed = $list->reversed();

        self::assertSame([5, 4, 3, 2, 1], $reversed->toArray());
    }

    #[Test]
    public function filterRemovesNonMatchingElements(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3, 4, 5, 6]);
        $evens = $list->filter(static fn (int $n): bool => $n % 2 === 0);

        self::assertSame([2, 4, 6], $evens->toArray());
    }

    #[Test]
    public function filterWithNoMatchesReturnsEmptyList(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3]);
        $result = $list->filter(static fn (int $n): bool => $n > 100);

        self::assertTrue($result->isEmpty());
    }

    #[Test]
    public function mapTransformsValues(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3]);
        $result = $list->map(static fn (int $n): int => $n * 2);

        self::assertSame([2, 4, 6], $result);
    }

    #[Test]
    public function reduceAccumulatesValues(): void
    {
        $list = SortedLinkedList::fromIntegerArray([1, 2, 3, 4, 5]);
        $sum = $list->reduce(static fn (int $acc, int $val): int => $acc + $val, 0);

        self::assertSame(15, $sum);
    }

    // ==================== Interface Implementations ====================

    #[Test]
    public function iteratorWorksInForeach(): void
    {
        $list = SortedLinkedList::fromIntegerArray([3, 1, 2]);
        $result = [];

        foreach ($list as $value) {
            $result[] = $value;
        }

        self::assertSame([1, 2, 3], $result);
    }

    #[Test]
    public function jsonSerializeReturnsArray(): void
    {
        $list = SortedLinkedList::fromIntegerArray([3, 1, 2]);

        self::assertSame('[1,2,3]', json_encode($list));
    }

    // ==================== Custom Comparators ====================

    #[Test]
    public function customDescendingComparator(): void
    {
        $list = SortedLinkedList::ofIntegers(
            new ReverseComparator(new IntegerComparator()),
        )
            ->add(1)
            ->add(5)
            ->add(3);

        self::assertSame([5, 3, 1], $list->toArray());
    }

    #[Test]
    public function customStringDescendingComparator(): void
    {
        $list = SortedLinkedList::ofStrings(
            new ReverseComparator(new StringComparator()),
        )
            ->add('apple')
            ->add('cherry')
            ->add('banana');

        self::assertSame(['cherry', 'banana', 'apple'], $list->toArray());
    }

    // ==================== Edge Cases ====================

    #[Test]
    public function singleElementList(): void
    {
        $list = SortedLinkedList::ofIntegers()->add(42);

        self::assertSame(42, $list->first());
        self::assertSame(42, $list->last());
        self::assertSame(1, $list->count());
        self::assertTrue($list->contains(42));
        self::assertSame([42], $list->toArray());
    }

    #[Test]
    public function largeNumberOfElements(): void
    {
        $list = SortedLinkedList::ofIntegers();
        $values = range(1, 100);
        shuffle($values);

        foreach ($values as $value) {
            $list = $list->add($value);
        }

        self::assertSame(range(1, 100), $list->toArray());
        self::assertSame(100, $list->count());
    }

    #[Test]
    public function negativeNumbers(): void
    {
        $list = SortedLinkedList::fromIntegerArray([-5, 3, -1, 0, 2, -3]);

        self::assertSame([-5, -3, -1, 0, 2, 3], $list->toArray());
    }

    #[Test]
    public function emptyStrings(): void
    {
        $list = SortedLinkedList::fromStringArray(['', 'a', '', 'b']);

        self::assertSame(['', '', 'a', 'b'], $list->toArray());
    }

    #[Test]
    public function stringsWithSpaces(): void
    {
        $list = SortedLinkedList::fromStringArray(['hello world', 'hello', 'world']);

        self::assertSame(['hello', 'hello world', 'world'], $list->toArray());
    }

    #[Test]
    public function immutabilityIsPreserved(): void
    {
        $list1 = SortedLinkedList::fromIntegerArray([1, 2, 3]);
        $list2 = $list1->add(4);
        $list3 = $list1->remove(2);

        self::assertSame([1, 2, 3], $list1->toArray());
        self::assertSame([1, 2, 3, 4], $list2->toArray());
        self::assertSame([1, 3], $list3->toArray());
    }

    #[Test]
    public function chainingOperations(): void
    {
        $result = SortedLinkedList::ofIntegers()
            ->add(5)
            ->add(1)
            ->add(3)
            ->remove(1)
            ->add(2)
            ->toArray();

        self::assertSame([2, 3, 5], $result);
    }
}
