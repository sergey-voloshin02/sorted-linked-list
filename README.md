## Requirements

- PHP 8.3 or higher

## Quick Start

```php
use SortedLinkedList\SortedLinkedList;

// Create a list of integers
$list = SortedLinkedList::ofIntegers()
    ->add(5)
    ->add(1)
    ->add(3);

echo $list->first(); // 1
echo $list->last();  // 5
print_r($list->toArray()); // [1, 3, 5]

// Create from array
$list = SortedLinkedList::fromIntegerArray([5, 2, 8, 1, 9]);
// Result: [1, 2, 5, 8, 9]

// Strings work the same way
$strings = SortedLinkedList::fromStringArray(['banana', 'apple', 'cherry']);
// Result: ['apple', 'banana', 'cherry']
```

## API Reference

### Factory Methods

```php
// Create empty lists
SortedLinkedList::ofIntegers(?ComparatorInterface $comparator = null): SortedLinkedList<int>
SortedLinkedList::ofStrings(?ComparatorInterface $comparator = null): SortedLinkedList<string>

// Create from arrays
SortedLinkedList::fromIntegerArray(array $values, ?ComparatorInterface $comparator = null): SortedLinkedList<int>
SortedLinkedList::fromStringArray(array $values, ?ComparatorInterface $comparator = null): SortedLinkedList<string>
SortedLinkedList::fromArray(array $values, ?ComparatorInterface $comparator = null): SortedLinkedList<int>|SortedLinkedList<string>
```

### Core Operations

All operations return a new instance (immutable):

```php
$list->add($value): SortedLinkedList       // Add value in sorted position
$list->remove($value): SortedLinkedList    // Remove first occurrence
$list->removeFirst(): SortedLinkedList     // Remove first element
$list->removeLast(): SortedLinkedList      // Remove last element
$list->merge($other): SortedLinkedList     // Merge two lists
```

### Query Methods

```php
$list->first(): int|string|null            // First element or null
$list->firstOrFail(): int|string           // First element or throws
$list->last(): int|string|null             // Last element or null
$list->lastOrFail(): int|string            // Last element or throws
$list->get(int $index): int|string|null    // Element at index
$list->contains($value): bool              // Check if value exists
$list->indexOf($value): ?int               // Find index of value
$list->isEmpty(): bool                     // Check if empty
$list->count(): int                        // Number of elements
$list->getValueType(): ValueType           // Integer or String
```

### Transformation

```php
$list->toArray(): array                              // Convert to array
$list->reversed(): SortedLinkedList                  // Reverse sort order
$list->filter(callable $predicate): SortedLinkedList // Filter elements
$list->map(callable $callback): array                // Transform to array
$list->reduce(callable $callback, $initial): mixed   // Reduce to single value
```

## Custom Comparators

Implement `ComparatorInterface` for custom sorting:

```php
use SortedLinkedList\SortedLinkedList;
use SortedLinkedList\Comparator\ComparatorInterface;
use SortedLinkedList\Comparator\ReverseComparator;
use SortedLinkedList\Comparator\IntegerComparator;

// Descending order using the built-in decorator
$descList = SortedLinkedList::ofIntegers(
    new ReverseComparator(new IntegerComparator())
);

$descList = $descList->add(1)->add(5)->add(3);
// Result: [5, 3, 1]

// Custom comparator example: sort by absolute value
final readonly class AbsoluteValueComparator implements ComparatorInterface
{
    public function compare(int|string $a, int|string $b): int
    {
        assert(is_int($a) && is_int($b));

        return abs($a) <=> abs($b);
    }
}

$list = SortedLinkedList::ofIntegers(new AbsoluteValueComparator())
    ->add(-5)
    ->add(3)
    ->add(-1);
// Result: [-1, 3, -5] (sorted by absolute value: 1, 3, 5)
```

### Built-in Comparators

| Comparator | Description |
|------------|-------------|
| `IntegerComparator` | Sorts integers in ascending order |
| `StringComparator` | Sorts strings in ascending lexicographic order |
| `ReverseComparator` | Decorator that reverses any comparator's order |

## Type Safety

The list enforces type homogeneity at runtime:

```php
use SortedLinkedList\SortedLinkedList;
use SortedLinkedList\Exception\TypeMismatchException;

$intList = SortedLinkedList::ofIntegers()->add(1);

try {
    $intList->add('string'); // Wrong type!
} catch (TypeMismatchException $e) {
    echo $e->getMessage();
    // "Type mismatch: expected int, got string. This list only accepts integer values."
}
```

## Iteration

The list implements `IteratorAggregate` and `Countable`:

```php
$list = SortedLinkedList::fromIntegerArray([3, 1, 2]);

// Foreach
foreach ($list as $value) {
    echo $value; // 1, 2, 3
}

// Count
echo count($list); // 3

// JSON serialization
echo json_encode($list); // [1,2,3]
```

## Immutability

All operations return new instances, leaving the original unchanged:

```php
$original = SortedLinkedList::fromIntegerArray([1, 2, 3]);
$modified = $original->add(4);

print_r($original->toArray()); // [1, 2, 3] - unchanged!
print_r($modified->toArray()); // [1, 2, 3, 4]
```

This makes the data structure safe to pass around without defensive copying.

## Exceptions

| Exception | When Thrown |
|-----------|-------------|
| `TypeMismatchException` | Adding a value of wrong type to a typed list |
| `EmptyListException` | Calling `firstOrFail()`, `lastOrFail()`, `removeFirst()`, or `removeLast()` on an empty list |

All exceptions extend `SortedLinkedListException` for convenient catching:

```php
use SortedLinkedList\Exception\SortedLinkedListException;

try {
    $list->firstOrFail();
} catch (SortedLinkedListException $e) {
    // Handle any library exception
}
```

## Complexity

| Operation | Time Complexity | Space Complexity |
|-----------|-----------------|------------------|
| `add()` | O(n) | O(n) |
| `remove()` | O(n) | O(n) |
| `first()` | O(1) | O(1) |
| `last()` | O(n) | O(1) |
| `get()` | O(n) | O(1) |
| `contains()` | O(n) | O(1) |
| `indexOf()` | O(n) | O(1) |
| `count()` | O(1) | O(1) |
| `toArray()` | O(n) | O(n) |
| `merge()` | O(n × m) | O(n + m) |
| `reversed()` | O(n^2) | O(n) |
| `filter()` | O(n^2) | O(n) |

Space complexity for mutating operations is O(n) because a new list is created (immutability).

## Development

```bash
composer install    # Install dependencies
composer test       # Run tests
composer analyse    # PHPStan level 9
composer cs-fix     # Fix code style
composer check      # Run all checks
```
