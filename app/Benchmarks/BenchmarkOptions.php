<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

use RuntimeException;

/**
 * Stores normalized benchmark CLI options.
 */
class BenchmarkOptions
{
    /**
     * Creates a benchmark option set.
     */
    public function __construct(
        private readonly int $iterations = 3,
        private readonly string $scale = 'medium',
        private readonly ?string $filter = null,
        private readonly bool $json = false,
    ) {
        if ($iterations < 1) {
            throw new RuntimeException('Iterations must be greater than zero.');
        }

        if (!in_array($scale, ['small', 'medium', 'large'], true)) {
            throw new RuntimeException(sprintf('Unsupported scale "%s". Expected small, medium, or large.', $scale));
        }
    }

    /**
     * Returns the iteration count.
     */
    public function iterations(): int
    {
        return $this->iterations;
    }

    /**
     * Returns the selected benchmark scale.
     */
    public function scale(): string
    {
        return $this->scale;
    }

    /**
     * Returns the optional case filter.
     */
    public function filter(): ?string
    {
        return $this->filter;
    }

    /**
     * Returns whether JSON output was requested.
     */
    public function json(): bool
    {
        return $this->json;
    }
}
