<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Parser;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Parser\ParserOptions;
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
     * Verifies optimized error tracking remains opt-in.
     */
    public function testSupportsOptimizedErrorTrackingAsAnOption(): void
    {
        $builder = GrammarBuilder::create();
        $grammar = $builder
            ->grammar('Start')
            ->rule('Start', $builder->choice($builder->literal('a'), $builder->literal('b')))
            ->build();

        $detailed = $grammar->parse('c');
        $optimized = $grammar->parse('c', options: new ParserOptions(optimizeErrors: true));

        self::assertSame(['"a"', '"b"'], $detailed->error()?->expected());
        self::assertCount(1, $optimized->error()?->expected() ?? []);
    }

    /**
     * Builds a grammar that accepts a single `a`.
     */
    private function simpleGrammar(): \EmanueleCoppola\PHPeg\Grammar\Grammar
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
    private function leftRecursiveGrammar(): \EmanueleCoppola\PHPeg\Grammar\Grammar
    {
        $builder = GrammarBuilder::create();

        return $builder
            ->grammar('Start')
            ->rule('Start', $builder->ref('Start'))
            ->build();
    }
}
