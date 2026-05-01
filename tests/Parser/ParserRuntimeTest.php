<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Parser;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Error\LeftRecursionException;
use EmanueleCoppola\PHPeg\Parser\BottomUp\BottomUpParseContext;
use EmanueleCoppola\PHPeg\Parser\InputBuffer;
use EmanueleCoppola\PHPeg\Parser\Packrat\PackratParseContext;
use PHPUnit\Framework\TestCase;

class ParserRuntimeTest extends TestCase
{
    /**
     * Verifies the packrat context still rejects direct left recursion.
     */
    public function testPackratContextRejectsDirectLeftRecursion(): void
    {
        $grammar = $this->leftRecursiveGrammar();
        $context = new PackratParseContext($grammar, new InputBuffer('1+2'));

        $this->expectException(LeftRecursionException::class);

        $context->matchRule('Expr', 0);
    }

    /**
     * Verifies the bottom-up context grows a left-recursive match.
     */
    public function testBottomUpContextGrowsDirectLeftRecursion(): void
    {
        $grammar = $this->leftRecursiveGrammar();
        $context = new BottomUpParseContext($grammar, new InputBuffer('1+2'));
        $result = $context->matchRule('Expr', 0);

        self::assertNotNull($result);
        self::assertSame(3, $result->endOffset());
    }

    /**
     * Builds a direct left-recursive grammar for runtime tests.
     */
    private function leftRecursiveGrammar(): \EmanueleCoppola\PHPeg\Grammar\Grammar
    {
        $builder = GrammarBuilder::create();

        return $builder
            ->grammar('Expr')
            ->rule('Expr', $builder->choice(
                $builder->seq($builder->ref('Expr'), $builder->literal('+'), $builder->ref('Number')),
                $builder->ref('Number'),
            ))
            ->rule('Number', $builder->oneOrMore($builder->charClass('[0-9]')))
            ->build();
    }
}
