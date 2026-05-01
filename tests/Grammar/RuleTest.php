<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Grammar;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Grammar\Grammar;
use EmanueleCoppola\PHPeg\Grammar\Rule;
use EmanueleCoppola\PHPeg\Expression\LiteralExpression;
use EmanueleCoppola\PHPeg\Parser\InputBuffer;
use EmanueleCoppola\PHPeg\Parser\Packrat\PackratParseContext;
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
        $context = new PackratParseContext($grammar, new InputBuffer('a'));

        self::assertSame('Start', $rule->name());
        self::assertInstanceOf(LiteralExpression::class, $rule->expression());
        self::assertSame(1, $rule->match($context, 0)?->endOffset());
    }
}
