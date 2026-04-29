<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks\Cases;

/**
 * Measures the same island parsing problem with a hand-written water rule.
 */
class ManualWaterIslandBenchmark extends AbstractIslandBenchmark
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'Island parsing with manual water';
    }

    /**
     * @inheritDoc
     */
    public function slug(): string
    {
        return 'manual-water-island';
    }

    /**
     * @inheritDoc
     */
    public function input(string $scale): string
    {
        return match ($scale) {
            'small' => $this->sparseInput(12),
            'medium' => $this->denseInput(72),
            'large' => $this->nestedWorstCaseInput(48, 8),
            default => $this->sparseInput(12),
        };
    }

    /**
     * @inheritDoc
     */
    protected function grammarSource(string $scale): string
    {
        return <<<'CLEANPEG'
Program = (Block / Water)* EOF
Block = "{" (Block / Water)* "}"
Water = r'[^{}]+'
Start = Program
CLEANPEG;
    }
}
