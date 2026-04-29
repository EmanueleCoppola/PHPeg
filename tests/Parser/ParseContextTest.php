<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Parser;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Error\LeftRecursionException;
use PHPUnit\Framework\TestCase;

class ParseContextTest extends TestCase
{
    /**
     * Verifies grammar/input accessors and memoized rule matching.
     */
    public function testMatchesRulesAndMemoizesResults(): void
    {
        $builder = GrammarBuilder::create();
        $grammar = $builder
            ->grammar('Start')
            ->rule('Start', $builder->seq($builder->literal('a'), $builder->eof()))
            ->build();
        $context = $grammar->contextFor('a');

        self::assertSame($grammar, $context->grammar());
        self::assertSame('a', $context->input()->text());
        self::assertSame($context->matchRule('Start', 0), $context->matchRule('Start', 0));
    }

    /**
     * Builds a useful error when a rule is missing or fails.
     */
    public function testBuildsFailureErrors(): void
    {
        $builder = GrammarBuilder::create();
        $grammar = $builder
            ->grammar('Start')
            ->rule('Start', $builder->literal('a'))
            ->build();
        $context = $grammar->contextFor('b');

        self::assertNull($context->matchRule('Missing', 0));
        self::assertStringContainsString('rule <Missing>', $context->error()->message());
    }
}