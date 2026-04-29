<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Loader\Peg;

use EmanueleCoppola\PHPeg\Loader\Peg\PegGrammarLoader;
use PHPUnit\Framework\TestCase;

class PegGrammarLoaderTest extends TestCase
{
    /**
     * Verifies PEG grammars can be loaded from a string and a file.
     */
    public function testLoadsPegGrammarsFromStringAndFile(): void
    {
        $loader = new PegGrammarLoader();
        $fromString = $loader->fromString('Start <- "a"');
        $fromFile = $loader->fromFile(__DIR__ . '/PegGrammarLoaderTest/recursive_language.peg');

        self::assertSame('Start', $fromString->startRule());
        self::assertTrue($fromString->parse('a')->isSuccess());
        self::assertSame('Program', $fromFile->startRule());
        self::assertTrue($fromFile->parse('block main { }')->isSuccess());
    }
}
