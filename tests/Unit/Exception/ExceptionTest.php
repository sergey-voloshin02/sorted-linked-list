<?php

declare(strict_types=1);

namespace SortedLinkedList\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SortedLinkedList\Exception\EmptyListException;
use SortedLinkedList\Exception\SortedLinkedListException;
use SortedLinkedList\Exception\TypeMismatchException;
use SortedLinkedList\ValueType;

#[CoversClass(SortedLinkedListException::class)]
#[CoversClass(TypeMismatchException::class)]
#[CoversClass(EmptyListException::class)]
final class ExceptionTest extends TestCase
{
    // ==================== TypeMismatchException ====================

    #[Test]
    public function typeMismatchExceptionExtendsBase(): void
    {
        $exception = TypeMismatchException::expectedInteger('string');

        self::assertInstanceOf(SortedLinkedListException::class, $exception);
        self::assertInstanceOf(RuntimeException::class, $exception);
    }

    #[Test]
    public function expectedIntegerCreatesCorrectException(): void
    {
        $exception = TypeMismatchException::expectedInteger('hello');

        self::assertStringContainsString('expected int', $exception->getMessage());
        self::assertStringContainsString('string', $exception->getMessage());
        self::assertSame(ValueType::Integer, $exception->getExpectedType());
        self::assertSame('string', $exception->getActualType());
    }

    #[Test]
    public function expectedStringCreatesCorrectException(): void
    {
        $exception = TypeMismatchException::expectedString(42);

        self::assertStringContainsString('expected string', $exception->getMessage());
        self::assertStringContainsString('int', $exception->getMessage());
        self::assertSame(ValueType::String, $exception->getExpectedType());
        self::assertSame('int', $exception->getActualType());
    }

    #[Test]
    public function forExpectedTypeWithInteger(): void
    {
        $exception = TypeMismatchException::forExpectedType(ValueType::Integer, 'test');

        self::assertSame(ValueType::Integer, $exception->getExpectedType());
    }

    #[Test]
    public function forExpectedTypeWithString(): void
    {
        $exception = TypeMismatchException::forExpectedType(ValueType::String, 123);

        self::assertSame(ValueType::String, $exception->getExpectedType());
    }

    #[Test]
    public function incompatibleListTypesCreatesCorrectException(): void
    {
        $exception = TypeMismatchException::incompatibleListTypes(ValueType::Integer, ValueType::String);

        self::assertStringContainsString('int', $exception->getMessage());
        self::assertStringContainsString('string', $exception->getMessage());
        self::assertStringContainsString('merge', $exception->getMessage());
        self::assertSame(ValueType::Integer, $exception->getExpectedType());
        self::assertSame('string', $exception->getActualType());
    }

    // ==================== EmptyListException ====================

    #[Test]
    public function emptyListExceptionExtendsBase(): void
    {
        $exception = EmptyListException::cannotGetFirst();

        self::assertInstanceOf(SortedLinkedListException::class, $exception);
        self::assertInstanceOf(RuntimeException::class, $exception);
    }

    #[Test]
    public function cannotGetFirstCreatesCorrectException(): void
    {
        $exception = EmptyListException::cannotGetFirst();

        self::assertStringContainsString('first', $exception->getMessage());
        self::assertStringContainsString('empty', $exception->getMessage());
    }

    #[Test]
    public function cannotGetLastCreatesCorrectException(): void
    {
        $exception = EmptyListException::cannotGetLast();

        self::assertStringContainsString('last', $exception->getMessage());
        self::assertStringContainsString('empty', $exception->getMessage());
    }

    #[Test]
    public function cannotRemoveCreatesCorrectException(): void
    {
        $exception = EmptyListException::cannotRemove();

        self::assertStringContainsString('remove', $exception->getMessage());
        self::assertStringContainsString('empty', $exception->getMessage());
    }

    // ==================== Catchability ====================

    #[Test]
    public function allExceptionsCanBeCaughtByBase(): void
    {
        $exceptions = [
            TypeMismatchException::expectedInteger('test'),
            TypeMismatchException::expectedString(123),
            EmptyListException::cannotGetFirst(),
            EmptyListException::cannotGetLast(),
            EmptyListException::cannotRemove(),
        ];

        foreach ($exceptions as $exception) {
            try {
                throw $exception;
            } catch (SortedLinkedListException $e) {
                self::assertInstanceOf(SortedLinkedListException::class, $e);
            }
        }
    }
}
