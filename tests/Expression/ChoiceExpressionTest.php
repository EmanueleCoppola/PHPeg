<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Expression;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Expression\ChoiceExpression;
use PHPUnit\Framework\TestCase;

class ChoiceExpressionTest extends TestCase
{
    /**
     * Verifies choice accessors and first-match behavior.
     */
    public function testMatchesTheFirstSuccessfulAlternative(): void
    {
        $builder = GrammarBuilder::create();
        $expression = new ChoiceExpression([$builder->literal('a'), $builder->literal('b')]);
        $grammar = $builder->grammar('Start')->rule('Start', $expression)->build();

        self::assertCount(2, $expression->alternatives());
        self::assertSame('choice', $expression->describe());
        self::assertTrue($grammar->parse('a')->isSuccess());
        self::assertTrue($grammar->parse('b')->isSuccess());
        self::assertFalse($grammar->parse('c')->isSuccess());
    }
}