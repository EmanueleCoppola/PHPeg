<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Loader\Peg;

use InvalidArgumentException;
use EmanueleCoppola\PHPeg\Grammar\Grammar;

/**
 * Loads PEG grammars from strings or files.
 */
class PegGrammarLoader
{
    /**
     * Loads a PEG grammar from raw source text.
     */
    public function fromString(string $source): Grammar
    {
        return PegGrammarParser::parse($source);
    }

    /**
     * Loads a PEG grammar from a file path.
     */
    public function fromFile(string $path): Grammar
    {
        $source = @file_get_contents($path);
        if ($source === false) {
            throw new InvalidArgumentException(sprintf('Unable to read PEG grammar file: %s', $path));
        }

        return $this->fromString($source);
    }
}
