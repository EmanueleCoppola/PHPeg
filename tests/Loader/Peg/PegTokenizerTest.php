<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Loader\Peg;

use EmanueleCoppola\PHPPeg\Loader\Peg\PegTokenizer;
use PHPUnit\Framework\TestCase;

class PegTokenizerTest extends TestCase
{
    /**
     * Verifies tokenization of a small PEG snippet.
     */
    public function testTokenizesPegSource(): void
    {
        $tokens = (new PegTokenizer('Start <- "a" / .'))->tokenize();

        self::assertSame(['IDENT', 'ARROW', 'LITERAL', 'SLASH', 'DOT', 'EOF'], array_map(static fn ($token): string => $token->type, $tokens));
        self::assertSame('Start', $tokens[0]->lexeme);
        self::assertSame('a', $tokens[2]->lexeme);
    }
}