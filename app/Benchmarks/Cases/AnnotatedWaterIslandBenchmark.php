<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks\Cases;

/**
 * Measures island parsing with water rules marked by annotations.
 */
class AnnotatedWaterIslandBenchmark extends AbstractIslandBenchmark
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'Island parsing with annotated water';
    }

    /**
     * @inheritDoc
     */
    public function slug(): string
    {
        return 'annotated-water-island';
    }

    /**
     * @inheritDoc
     */
    public function input(string $scale): string
    {
        return match ($scale) {
            'small' => $this->realisticMixedInput(2, 1),
            'medium' => $this->realisticMixedInput(4, 1),
            'large' => $this->realisticMixedInput(6, 1),
            default => $this->realisticMixedInput(2, 1),
        };
    }

    /**
     * @inheritDoc
     */
    protected function grammarSource(string $scale): string
    {
        return <<<'CLEANPEG'
Program = (Block / ~)* EOF
Block = "{" (Block / ~)* "}"
@water
Comment = r'//[^\n]*(?:\n|$)'
@water
String = r'"(?:\\.|[^"])*"'
@water
Word = r'[A-Za-z_][A-Za-z0-9_]*'
@water
Number = r'\d+'
@water
Whitespace = r'[ \t\r\n]+'
@water
Punctuation = r'[=;:,./-]+'
Start = Program
CLEANPEG;
    }
}
