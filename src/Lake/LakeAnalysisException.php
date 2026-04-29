<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Lake;

use RuntimeException;

/**
 * Signals that lake-stop analysis could not determine a safe stop set.
 */
class LakeAnalysisException extends RuntimeException
{
}
