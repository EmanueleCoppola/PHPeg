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
Program = (Block / ~) * EOF
Block = "{" (Block / ~) * "}"
Start = Program
CLEANPEG;
    }
}
