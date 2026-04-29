<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Grammar;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Grammar\Grammar;
use EmanueleCoppola\PHPeg\Grammar\Rule;
use EmanueleCoppola\PHPeg\Expression\LiteralExpression;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    /**
     * Verifies rule accessors and matching behavior.
     */
    public function testExposesNameExpressionAndMatch(): void
    {
        $rule = new Rule('Start', new LiteralExpression('a'));
        $grammar = new Grammar(['Start' => $rule], 'Start');

        self::assertSame('Start', $rule->name());
        self::assertInstanceOf(LiteralExpression::class, $rule->expression());
        self::assertSame(1, $rule->match($grammar->contextFor('a'), 0)?->endOffset());
    }
}
