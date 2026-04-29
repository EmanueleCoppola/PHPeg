<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks\Cases;

use EmanueleCoppola\PHPeg\Result\ParseResult;

/**
 * Measures parsing against a grammar with many long common prefixes.
 */
class BacktrackingHeavyBenchmark extends AbstractBenchmarkCase
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'Backtracking-heavy grammar';
    }

    /**
     * @inheritDoc
     */
    public function slug(): string
    {
        return 'backtracking';
    }

    /**
     * @inheritDoc
     */
    public function input(string $scale): string
    {
        $prefixLength = $this->sizeForScale($scale, [
            'small' => 4096,
            'medium' => 32768,
            'large' => 131072,
        ]);

        return str_repeat('a', $prefixLength) . 'b';
    }

    /**
     * @inheritDoc
     */
    public function validate(ParseResult $result, string $input): void
    {
        $this->assertSuccessfulFullMatch($result, $input);
    }

    /**
     * @inheritDoc
     */
    protected function grammarSource(string $scale): string
    {
        $prefixLength = $this->sizeForScale($scale, [
            'small' => 4096,
            'medium' => 32768,
            'large' => 131072,
        ]);

        $alternatives = [];
        $alternativeCount = $this->sizeForScale($scale, [
            'small' => 24,
            'medium' => 64,
            'large' => 96,
        ]);

        for ($index = 0; $index < $alternativeCount; $index++) {
            $suffix = sprintf('x%02d', $index);
            $alternatives[] = '"' . str_repeat('a', $prefixLength) . $suffix . '"';
        }

        $alternatives[] = '"' . str_repeat('a', $prefixLength) . 'b"';

        return sprintf(
            "Candidate = %s\nStart = Candidate EOF\n",
            implode(' / ', $alternatives),
        );
    }

    /**
     * @inheritDoc
     */
    protected function startRule(): string
    {
        return 'Start';
    }
}
