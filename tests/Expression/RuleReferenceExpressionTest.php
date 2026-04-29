<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Expression;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Expression\RuleReferenceExpression;
use PHPUnit\Framework\TestCase;

class RuleReferenceExpressionTest extends TestCase
{
    /**
     * Verifies rule reference accessors and matching.
     */
    public function testMatchesTheReferencedRule(): void
    {
        $builder = GrammarBuilder::create();
        $expression = new RuleReferenceExpression('Value');
        $grammar = $builder
            ->grammar('Start')
            ->rule('Start', $expression)
            ->rule('Value', $builder->literal('a'))
            ->build();

        self::assertSame('Value', $expression->ruleName());
        self::assertSame('<Value>', $expression->describe());
        self::assertTrue($grammar->parse('a')->isSuccess());
    }
}