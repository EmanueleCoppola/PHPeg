<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Ast;

use EmanueleCoppola\PHPPeg\Ast\AstSelectorParser;
use EmanueleCoppola\PHPPeg\Error\AstQueryError;
use PHPUnit\Framework\TestCase;

class AstSelectorParserTest extends TestCase
{
    /**
     * Parses a selector into named steps.
     */
    public function testParsesSelectors(): void
    {
        $selector = AstSelectorParser::parse('Block[name="server"] > Directive:first');

        self::assertCount(2, $selector->steps());
        self::assertSame('Block', $selector->steps()[0]->name());
        self::assertSame('Directive', $selector->steps()[1]->name());
        self::assertSame('child', $selector->steps()[1]->combinator());
        self::assertSame(['name' => 'server'], $selector->steps()[0]->attributes());
        self::assertSame('first', $selector->steps()[1]->pseudo());
    }

    /**
     * Rejects malformed selectors.
     */
    public function testRejectsInvalidSelectors(): void
    {
        $this->expectException(AstQueryError::class);

        AstSelectorParser::parse('Block[');
    }
}