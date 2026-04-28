<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Parser;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * Verifies successful parsing and parse result metadata.
     */
    public function testParsesSuccessfulInput(): void
    {
        $grammar = $this->simpleGrammar();
        $result = $grammar->parse('a');

        self::assertTrue($result->isSuccess());
        self::assertSame('a', $result->matchedText());
        self::assertSame(1, $result->finalOffset());
        self::assertSame('Start', $result->node()?->name());
    }

    /**
     * Rejects left-recursive grammars with a clear error.
     */
    public function testReportsLeftRecursion(): void
    {
        $result = $this->leftRecursiveGrammar()->parse('a');

        self::assertFalse($result->isSuccess());
        self::assertStringContainsString('Left-recursive rule detected', $result->error()?->message() ?? '');
    }

    /**
     * Builds a grammar that accepts a single `a`.
     */
    private function simpleGrammar(): \EmanueleCoppola\PHPPeg\Grammar\Grammar
    {
        $builder = GrammarBuilder::create();

        return $builder
            ->grammar('Start')
            ->rule('Start', $builder->seq($builder->literal('a'), $builder->eof()))
            ->build();
    }

    /**
     * Builds a grammar that triggers direct left recursion.
     */
    private function leftRecursiveGrammar(): \EmanueleCoppola\PHPPeg\Grammar\Grammar
    {
        $builder = GrammarBuilder::create();

        return $builder
            ->grammar('Start')
            ->rule('Start', $builder->ref('Start'))
            ->build();
    }
}
