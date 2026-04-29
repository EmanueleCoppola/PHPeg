<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Parser;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Parser\ParserOptions;
use PHPUnit\Framework\TestCase;

class LazyNodeTextTest extends TestCase
{
    /**
     * Verifies lazy node text preserves the observable AST text.
     */
    public function testLazyNodeTextPreservesNodeText(): void
    {
        $builder = GrammarBuilder::create();
        $grammar = $builder
            ->grammar('Start')
            ->rule('Start', $builder->seq($builder->literal('a'), $builder->literal('b'), $builder->eof()))
            ->build();

        $result = $grammar->parse('ab', options: ParserOptions::defaults()->withLazyNodeText(true));

        self::assertTrue($result->isSuccess());
        self::assertSame('ab', $result->node()?->text());
        self::assertSame('ab', $result->node()?->originalText());
        self::assertSame('ab', $result->matchedText());
    }
}
