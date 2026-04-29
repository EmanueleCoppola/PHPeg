<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks\Cases;

/**
 * Measures island parsing with native lake nodes.
 */
class LakeIslandBenchmark extends AbstractIslandBenchmark
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'Island parsing with lakes';
    }

    /**
     * @inheritDoc
     */
    public function slug(): string
    {
        return 'lake-island';
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
Program = (Block / ~) * EOF
Block = "{" (Block / ~) * "}"
Start = Program
CLEANPEG;
    }
}
