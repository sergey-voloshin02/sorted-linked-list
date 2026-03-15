<?php

declare(strict_types=1);

namespace SortedLinkedList\Tests\Unit\Comparator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SortedLinkedList\Comparator\IntegerComparator;
use SortedLinkedList\Comparator\ReverseComparator;
use SortedLinkedList\Comparator\StringComparator;

#[CoversClass(IntegerComparator::class)]
#[CoversClass(StringComparator::class)]
#[CoversClass(ReverseComparator::class)]
final class ComparatorTest extends TestCase
{
    // ==================== IntegerComparator ====================

    #[Test]
    #[DataProvider('integerComparisonProvider')]
    public function integerComparatorComparesCorrectly(int $a, int $b, int $expectedSign): void
    {
        $comparator = new IntegerComparator();
        $result = $comparator->compare($a, $b);

        self::assertSame($expectedSign, $this->sign($result));
    }

    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function integerComparisonProvider(): iterable
    {
        yield 'a less than b' => [1, 5, -1];
        yield 'a greater than b' => [5, 1, 1];
        yield 'a equals b' => [3, 3, 0];
        yield 'negative numbers' => [-5, -1, -1];
        yield 'zero comparison' => [0, 0, 0];
        yield 'positive vs negative' => [1, -1, 1];
        yield 'large numbers' => [1000000, 999999, 1];
    }

    // ==================== StringComparator ====================

    #[Test]
    #[DataProvider('stringComparisonProvider')]
    public function stringComparatorComparesCorrectly(string $a, string $b, int $expectedSign): void
    {
        $comparator = new StringComparator();
        $result = $comparator->compare($a, $b);

        self::assertSame($expectedSign, $this->sign($result));
    }

    /**
     * @return iterable<string, array{string, string, int}>
     */
    public static function stringComparisonProvider(): iterable
    {
        yield 'alphabetical order' => ['apple', 'banana', -1];
        yield 'reverse alphabetical' => ['banana', 'apple', 1];
        yield 'equal strings' => ['hello', 'hello', 0];
        yield 'empty strings' => ['', '', 0];
        yield 'empty vs non-empty' => ['', 'a', -1];
        yield 'case sensitive' => ['Apple', 'apple', -1]; // uppercase < lowercase in ASCII
        yield 'numbers in strings' => ['a1', 'a2', -1];
        yield 'same prefix' => ['hello', 'hello world', -1];
    }

    // ==================== ReverseComparator ====================

    #[Test]
    public function reverseComparatorInvertsIntegerOrder(): void
    {
        $inner = new IntegerComparator();
        $reverse = new ReverseComparator($inner);

        self::assertTrue($reverse->compare(1, 5) > 0); // Reversed: 1 > 5
        self::assertTrue($reverse->compare(5, 1) < 0); // Reversed: 5 < 1
        self::assertSame(0, $reverse->compare(3, 3)); // Equal stays equal
    }

    #[Test]
    public function reverseComparatorInvertsStringOrder(): void
    {
        $inner = new StringComparator();
        $reverse = new ReverseComparator($inner);

        self::assertTrue($reverse->compare('apple', 'banana') > 0);
        self::assertTrue($reverse->compare('banana', 'apple') < 0);
        self::assertSame(0, $reverse->compare('same', 'same'));
    }

    #[Test]
    public function doubleReverseRestoresOriginalOrder(): void
    {
        $inner = new IntegerComparator();
        $reversed = new ReverseComparator($inner);
        $doubleReversed = new ReverseComparator($reversed);

        self::assertSame(
            $this->sign($inner->compare(1, 5)),
            $this->sign($doubleReversed->compare(1, 5)),
        );
    }

    // ==================== Helper ====================

    /**
     * Returns the sign of a number: -1, 0, or 1.
     */
    private function sign(int $value): int
    {
        return $value <=> 0;
    }
}
