<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Feature;

use EmanueleCoppola\PHPPeg\Loader\CleanPeg\CleanPegGrammarLoader;
use PHPUnit\Framework\TestCase;

class CleanPegGrammarLoaderTest extends TestCase
{
    public function testCanLoadCalculatorGrammarFromString(): void
    {
        $grammar = (new CleanPegGrammarLoader())->fromString($this->calculatorGrammar(), startRule: 'calc');

        self::assertTrue($grammar->parse('1+2')->isSuccess());
    }

    public function testCanLoadCalculatorGrammarFromFile(): void
    {
        $grammar = (new CleanPegGrammarLoader())->fromFile(
            __DIR__ . '/CleanPegGrammarLoaderTest/calculator.cleanpeg',
            startRule: 'calc',
        );

        self::assertTrue($grammar->parse('1+2')->isSuccess());
    }

    public function testParsesOnePlusTwo(): void
    {
        self::assertTrue($this->calculator()->parse('1+2')->isSuccess());
    }

    public function testParsesOnePlusTwoTimesThree(): void
    {
        self::assertTrue($this->calculator()->parse('1 + 2 * 3')->isSuccess());
    }

    public function testParsesParenthesizedExpression(): void
    {
        self::assertTrue($this->calculator()->parse('(1 + 2) * 3')->isSuccess());
    }

    public function testParsesSignedDecimalExpression(): void
    {
        self::assertTrue($this->calculator()->parse('-10 + 2.5')->isSuccess());
    }

    public function testParsesUnaryPlusWithGrouping(): void
    {
        self::assertTrue($this->calculator()->parse('+4 * (3 - 1)')->isSuccess());
    }

    public function testFailsOnIncompleteExpression(): void
    {
        self::assertFalse($this->calculator()->parse('1 +')->isSuccess());
    }

    public function testFailsOnInvalidTrailingInputWhenEofIsPresent(): void
    {
        self::assertFalse($this->calculator()->parse('1 + 2 abc')->isSuccess());
    }

    public function testSupportsGroupedChoices(): void
    {
        $grammar = (new CleanPegGrammarLoader())->fromString(<<<'CLEANPEG'
start = ("a" / "b") "c" EOF
CLEANPEG, startRule: 'start');

        self::assertTrue($grammar->parse('ac')->isSuccess());
        self::assertTrue($grammar->parse('bc')->isSuccess());
        self::assertFalse($grammar->parse('cc')->isSuccess());
    }

    public function testSupportsQuantifiers(): void
    {
        $grammar = (new CleanPegGrammarLoader())->fromString(<<<'CLEANPEG'
start = "a"? "b"* "c"+ EOF
CLEANPEG, startRule: 'start');

        self::assertTrue($grammar->parse('c')->isSuccess());
        self::assertTrue($grammar->parse('abbbcc')->isSuccess());
        self::assertFalse($grammar->parse('abbb')->isSuccess());
    }

    public function testSupportsComments(): void
    {
        $grammar = (new CleanPegGrammarLoader())->fromString(<<<'CLEANPEG'
# calculator with comments
number = r'\d+' # integer
start = number EOF
CLEANPEG, startRule: 'start');

        self::assertTrue($grammar->parse('123')->isSuccess());
    }

    private function calculator(): \EmanueleCoppola\PHPPeg\Grammar\Grammar
    {
        return (new CleanPegGrammarLoader())->fromString($this->calculatorGrammar(), startRule: 'calc');
    }

    private function calculatorGrammar(): string
    {
        return <<<'CLEANPEG'
number = r'\d*\.\d*|\d+'
factor = ("+" / "-")? (number / "(" expression ")")
term = factor (("*" / "/") factor)*
expression = term (("+" / "-") term)*
calc = expression+ EOF
CLEANPEG;
    }
}
