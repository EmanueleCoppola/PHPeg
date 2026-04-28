<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Result;

use EmanueleCoppola\PHPPeg\Ast\AstNodeFactory;
use EmanueleCoppola\PHPPeg\Error\ParseError;
use EmanueleCoppola\PHPPeg\Result\ParseResult;
use PHPUnit\Framework\TestCase;

class ParseResultTest extends TestCase
{
    /**
     * Verifies successful and failed parse results.
     */
    public function testExposesParseResultState(): void
    {
        $factory = new AstNodeFactory();
        $node = $factory->node('Start', [], 'a');
        $success = ParseResult::success(1, 'a', $node);
        $failure = ParseResult::failure(0, '', new ParseError(0, 1, 1, ['"a"'], '"b"'));

        self::assertTrue($success->isSuccess());
        self::assertSame($node, $success->node());
        self::assertSame('a', $success->matchedText());
        self::assertSame(1, $success->finalOffset());
        self::assertFalse($failure->isSuccess());
        self::assertNull($failure->node());
        self::assertInstanceOf(ParseError::class, $failure->error());
    }
}