<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

use EmanueleCoppola\PHPeg\Parser\ParserOptions;
use RuntimeException;

/**
 * Describes one parser benchmark mode.
 */
class BenchmarkMode
{
    /**
     * Creates a benchmark mode definition.
     */
    public function __construct(
        private readonly string $slug,
        private readonly string $name,
        private readonly ParserOptions $parserOptions,
        private readonly string $tradeoff,
    ) {
    }

    /**
     * Returns the default benchmark mode list.
     *
     * @return list<self>
     */
    public static function defaults(): array
    {
        return [
            new self(
                'default',
                'Default parser settings',
                ParserOptions::defaults(),
                'Balanced default with full errors, unbounded memoization, and lazy node text.',
            ),
            new self(
                'speed',
                'Speed optimized',
                ParserOptions::defaults()
                    ->withMemoization(true)
                    ->withLazyNodeText(true)
                    ->withOptimizeErrors(true)
                    ->withReuseEmptyMatches(true),
                'Combines the strongest successful-parse optimizations for throughput-oriented parsing.',
            ),
            new self(
                'memory',
                'Memory optimized',
                ParserOptions::defaults()->withMemoization(false)->withLazyNodeText(true),
                'Disables memoization and avoids eager node text copies to reduce parser memory pressure.',
            ),
        ];
    }

    /**
     * Returns the benchmark mode by slug.
     *
     * @param list<string> $slugs
     * @return list<self>
     */
    public static function fromSlugs(array $slugs): array
    {
        $available = [];
        foreach (self::defaults() as $mode) {
            $available[$mode->slug()] = $mode;
        }

        $selected = [];
        foreach ($slugs as $slug) {
            if (!isset($available[$slug])) {
                throw new RuntimeException(sprintf('Unsupported benchmark mode "%s".', $slug));
            }

            $selected[] = $available[$slug];
        }

        return $selected;
    }

    /**
     * Returns the machine-readable mode slug.
     */
    public function slug(): string
    {
        return $this->slug;
    }

    /**
     * Returns the human-readable mode name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the parser options used by this mode.
     */
    public function parserOptions(): ParserOptions
    {
        return $this->parserOptions;
    }

    /**
     * Returns the primary tradeoff description.
     */
    public function tradeoff(): string
    {
        return $this->tradeoff;
    }
}
