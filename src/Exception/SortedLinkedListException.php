<?php

declare(strict_types=1);

namespace SortedLinkedList\Exception;

use RuntimeException;

/**
 * Base exception for all SortedLinkedList errors.
 *
 * This abstract class serves as a catch-all for library-specific exceptions,
 * allowing consumers to catch any SortedLinkedList exception with a single catch block.
 */
abstract class SortedLinkedListException extends RuntimeException
{
}
