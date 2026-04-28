<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Loader\CleanPeg;

use EmanueleCoppola\PHPPeg\Loader\CleanPeg\CleanPegGrammarLoader;
use PHPUnit\Framework\TestCase;

class CleanPegGrammarLoaderTest extends TestCase
{
    /**
     * Verifies CleanPeg grammars can be loaded from a string and a file.
     */
    public function testLoadsCleanPegGrammarsFromStringAndFile(): void
    {
        $loader = new CleanPegGrammarLoader();
        $fromString = $loader->fromString("Start = \"a\"\n", 'Start');
        $fixtureDir = __DIR__ . '/CleanPegGrammarLoaderTest';
        $contents = file_get_contents($fixtureDir . '/json-file.json');
        self::assertNotFalse($contents);
        $fromFile = $loader->fromFile($fixtureDir . '/json.cleanpeg', startRule: 'Json');

        self::assertSame('Start', $fromString->startRule());
        self::assertTrue($fromString->parse('a')->isSuccess());
        self::assertSame('Json', $fromFile->startRule());
        self::assertTrue($fromFile->parse($contents, 'Json')->isSuccess());
    }
}
