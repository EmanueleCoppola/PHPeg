<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Builder;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Expression\AndPredicateExpression;
use EmanueleCoppola\PHPeg\Expression\AnyCharacterExpression;
use EmanueleCoppola\PHPeg\Expression\ChoiceExpression;
use EmanueleCoppola\PHPeg\Expression\EndOfInputExpression;
use EmanueleCoppola\PHPeg\Expression\LiteralExpression;
use EmanueleCoppola\PHPeg\Expression\LakeExpression;
use EmanueleCoppola\PHPeg\Expression\NotPredicateExpression;
use EmanueleCoppola\PHPeg\Expression\OneOrMoreExpression;
use EmanueleCoppola\PHPeg\Expression\OptionalExpression;
use EmanueleCoppola\PHPeg\Expression\RegexExpression;
use EmanueleCoppola\PHPeg\Expression\RuleReferenceExpression;
use EmanueleCoppola\PHPeg\Expression\SequenceExpression;
use EmanueleCoppola\PHPeg\Expression\ZeroOrMoreExpression;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GrammarBuilderTest extends TestCase
{
    /**
     * Verifies builder aliases and expression factories.
     */
    public function testCreatesGrammarPieces(): void
    {
        $builder = GrammarBuilder::create();
        $grammar = $builder
            ->grammar('Start')
            ->rule('Start', $builder->seq($builder->literal('a'), $builder->eof()))
            ->build();

        self::assertSame('Start', $grammar->startRule());
        self::assertSame('sequence', $grammar->rule('Start')?->expression()->describe());
        self::assertInstanceOf(LiteralExpression::class, $builder->literal('x'));
        self::assertInstanceOf(LakeExpression::class, $builder->lake());
        self::assertInstanceOf(LakeExpression::class, $builder->lake('BodyWater'));
        self::assertInstanceOf(RegexExpression::class, $builder->regex('[a-z]+'));
        self::assertInstanceOf(SequenceExpression::class, $builder->seq($builder->literal('a'), $builder->literal('b')));
        self::assertInstanceOf(ChoiceExpression::class, $builder->choice($builder->literal('a'), $builder->literal('b')));
        self::assertInstanceOf(ZeroOrMoreExpression::class, $builder->zeroOrMore($builder->literal('a')));
        self::assertInstanceOf(OneOrMoreExpression::class, $builder->oneOrMore($builder->literal('a')));
        self::assertInstanceOf(OptionalExpression::class, $builder->optional($builder->literal('a')));
        self::assertInstanceOf(RuleReferenceExpression::class, $builder->ref('Start'));
        self::assertInstanceOf(AnyCharacterExpression::class, $builder->any());
        self::assertInstanceOf(EndOfInputExpression::class, $builder->eof());
        self::assertInstanceOf(AndPredicateExpression::class, $builder->and($builder->literal('a')));
        self::assertInstanceOf(NotPredicateExpression::class, $builder->not($builder->literal('a')));
        self::assertInstanceOf(OneOrMoreExpression::class, $builder->one($builder->literal('a')));
        self::assertInstanceOf(ZeroOrMoreExpression::class, $builder->many($builder->literal('a')));
        self::assertInstanceOf(OptionalExpression::class, $builder->maybe($builder->literal('a')));
        self::assertInstanceOf(ChoiceExpression::class, $builder->or($builder->literal('a'), $builder->literal('b')));
        self::assertSame('a', $grammar->parse('a')->matchedText());
    }

    /**
     * Rejects building without a start rule.
     */
    public function testRejectsMissingStartRule(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GrammarBuilder::create()->build();
    }
}
