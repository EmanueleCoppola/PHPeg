<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Loader\CleanPeg;

use InvalidArgumentException;
use EmanueleCoppola\PHPPeg\Grammar\Grammar;

/**
 * Loads grammars written in CleanPeg syntax.
 */
class CleanPegGrammarLoader
{
    public function __construct(
        private readonly ?string $skipPattern = '[ \t\r\n]*',
    ) {
    }

    /**
     * Loads a CleanPeg grammar from raw source text.
     */
    public function fromString(string $source, ?string $startRule = null): Grammar
    {
        return CleanPegGrammarParser::parse($source, $startRule, $this->skipPattern);
    }

    /**
     * Loads a CleanPeg grammar from a file path.
     */
    public function fromFile(string $path, ?string $startRule = null): Grammar
    {
        $source = @file_get_contents($path);
        if ($source === false) {
            throw new InvalidArgumentException(sprintf('Unable to read CleanPeg grammar file: %s', $path));
        }

        return $this->fromString($source, $startRule);
    }
}
