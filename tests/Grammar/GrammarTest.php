<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Grammar;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use PHPUnit\Framework\TestCase;

class GrammarTest extends TestCase
{
    /**
     * Verifies grammar lookup, parsing, and context creation.
     */
    public function testExposesRulesAndParsing(): void
    {
        $builder = GrammarBuilder::create();
        $grammar = $builder
            ->grammar('Start')
            ->rule('Start', $builder->seq($builder->literal('a'), $builder->ref('Tail')))
            ->rule('Tail', $builder->literal('b'))
            ->build();

        self::assertSame('Start', $grammar->startRule());
        self::assertCount(2, $grammar->rules());
        self::assertNotNull($grammar->rule('Tail'));
        self::assertNull($grammar->rule('Missing'));
        self::assertSame('ab', $grammar->parse('ab')->matchedText());
        self::assertSame('ab', $grammar->parseDocument('ab')->source());
        self::assertSame('ab', $grammar->contextFor('ab')->input()->text());
    }
}