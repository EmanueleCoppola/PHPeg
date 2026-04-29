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
}
