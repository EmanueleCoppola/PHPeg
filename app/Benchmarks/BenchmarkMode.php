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
                'Compatibility mode with full errors and unbounded memoization.',
            ),
            new self(
                'memoized',
                'Memoization enabled',
                ParserOptions::defaults()->withMemoization(true),
                'Explicit packrat-style memoization for grammars that revisit the same rule and offset.',
            ),
            new self(
                'fast',
                'Fast mode',
                ParserOptions::fast(),
                'Faster success path by reusing zero-width matches and reducing detailed error bookkeeping.',
            ),
            new self(
                'memory',
                'Memory optimized mode',
                ParserOptions::memoryOptimized(),
                'Lower memory pressure by avoiding memoization caches and extra zero-width match reuse.',
            ),
            new self(
                'limited-cache',
                'Memoization with limited cache',
                ParserOptions::defaults()->withMaxCacheEntries(2048),
                'Caps memoization growth to bound memory usage at the cost of extra recomputation.',
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
