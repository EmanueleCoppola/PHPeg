<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Loader\CleanPeg;

use EmanueleCoppola\PHPPeg\Loader\CleanPeg\CleanPegTokenizer;
use PHPUnit\Framework\TestCase;

class CleanPegTokenizerTest extends TestCase
{
    /**
     * Verifies tokenization of a small CleanPeg snippet.
     */
    public function testTokenizesCleanPegSource(): void
    {
        $tokens = (new CleanPegTokenizer("Json = \"a\"\n"))->tokenize();

        self::assertSame(['IDENT', 'EQUAL', 'STRING', 'NEWLINE', 'EOF'], array_map(static fn ($token): string => $token->type, $tokens));
        self::assertSame('Json', $tokens[0]->lexeme);
        self::assertSame('a', $tokens[2]->lexeme);
    }
}