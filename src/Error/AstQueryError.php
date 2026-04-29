<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Error;

use InvalidArgumentException;

/**
 * Raised when an AST selector string is invalid.
 */
class AstQueryError extends InvalidArgumentException
{
}
