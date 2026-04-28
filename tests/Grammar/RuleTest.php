<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Grammar;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPPeg\Grammar\Grammar;
use EmanueleCoppola\PHPPeg\Grammar\Rule;
use EmanueleCoppola\PHPPeg\Expression\LiteralExpression;
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
