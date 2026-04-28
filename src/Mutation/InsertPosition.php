<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Mutation;

/**
 * Supported AST insertion positions.
 */
enum InsertPosition
{
    case Before;
    case After;
    case Prepend;
    case Append;
}
