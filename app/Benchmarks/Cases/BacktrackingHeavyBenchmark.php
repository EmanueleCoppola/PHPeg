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
            'medium' => 16384,
            'large' => 65536,
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
        $alternatives = [];
        $alternativeCount = $this->sizeForScale($scale, [
            'small' => 16,
            'medium' => 64,
            'large' => 96,
        ]);

        for ($index = 0; $index < $alternativeCount; $index++) {
            $suffix = sprintf('x%02d', $index);
            $alternatives[] = 'SharedTail "' . $suffix . '"';
        }

        $alternatives[] = 'SharedTail "b"';

        return sprintf(
            "SharedTail = \"a\" SharedTail / \"\"\nCandidate = %s\nStart = Candidate EOF\n",
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
