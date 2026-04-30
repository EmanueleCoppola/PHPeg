<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks\Cases;

use EmanueleCoppola\PHPeg\Result\ParseResult;

/**
 * Shared helpers for island-parsing benchmarks.
 */
abstract class AbstractIslandBenchmark extends AbstractBenchmarkCase
{
    /**
     * @inheritDoc
     */
    protected function startRule(): string
    {
        return 'Program';
    }

    /**
     * @inheritDoc
     */
    public function validate(ParseResult $result, string $input): void
    {
        $this->assertSuccessfulFullMatch($result, $input);
    }

    /**
     * Builds a sparse document with several blocks separated by water.
     */
    protected function sparseInput(int $count): string
    {
        $segments = [];

        for ($index = 0; $index < $count; $index++) {
            $segments[] = sprintf('head%03d', $index);
            $segments[] = sprintf('{block%03d}', $index);
        }

        $segments[] = 'tail';

        return implode(' ', $segments);
    }

    /**
     * Builds a dense document with many tiny islands.
     */
    protected function denseInput(int $count): string
    {
        $segments = [];

        for ($index = 0; $index < $count; $index++) {
            $segments[] = sprintf('x{%d}y', $index % 10);
        }

        return implode('', $segments);
    }

    /**
     * Builds a nested document with a long leading water run.
     */
    protected function nestedWorstCaseInput(int $prefixLength, int $depth): string
    {
        $input = str_repeat('water-', $prefixLength);

        for ($index = 0; $index < $depth; $index++) {
            $input .= sprintf('{level%02d', $index);
        }

        $input .= 'body';

        for ($index = $depth - 1; $index >= 0; $index--) {
            $input .= sprintf('tail%02d}', $index);
        }

        $input .= str_repeat('-water', intdiv($prefixLength, 2));

        return $input;
    }

    /**
     * Builds a mixed document with config-like water, quoted strings, comments, and nested blocks.
     */
    protected function realisticMixedInput(int $sections, int $depth): string
    {
        $input = '';

        for ($index = 0; $index < $sections; $index++) {
            $input .= $this->realisticWaterLine($index);
            $input .= $this->realisticBlock($index, $depth);
            $input .= $this->realisticTrailerLine($index);
        }

        return $input;
    }

    /**
     * Builds one realistic water line that should be swallowed by annotated water rules.
     */
    private function realisticWaterLine(int $index): string
    {
        return sprintf(
            'config_%03d = "value_%03d with spaces"; // note %03d and punctuation; ' .
            'path/%03d/item-%03d, next=%03d' . "\n",
            $index,
            $index,
            $index,
            $index,
            $index,
            $index,
        );
    }

    /**
     * Builds one nested block that remains structured while surrounding water is consumed separately.
     */
    private function realisticBlock(int $index, int $depth): string
    {
        $block = '';
        for ($level = 0; $level < $depth; $level++) {
            $block .= sprintf('{section_%03d_level_%d ', $index, $level);
        }

        $block .= sprintf('payload_%03d "quoted { text }" // inner comment %03d ', $index, $index);

        for ($level = $depth - 1; $level >= 0; $level--) {
            $block .= sprintf('tail_%03d_level_%d}', $index, $level);
        }

        $block .= "\n";

        return $block;
    }

    /**
     * Builds a trailing line that keeps the document varied and realistic.
     */
    private function realisticTrailerLine(int $index): string
    {
        return sprintf(
            'trailer_%03d plain-text segment, more words, and a number %03d' . "\n",
            $index,
            $index * 17,
        );
    }
}
