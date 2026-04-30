<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Ast;

/**
 * Represents a single selector step and its combinator.
 */
class AstSelectorStep
{
    /**
     * @param array<string, string> $attributes
     */
    public function __construct(
        private readonly string $name,
        private readonly string $combinator,
        private readonly array $attributes = [],
        private readonly ?string $pseudo = null,
        private readonly ?int $pseudoArgument = null,
    ) {
    }

    /**
     * Returns the stored name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the selector combinator.
     */
    public function combinator(): string
    {
        return $this->combinator;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns the pseudo-selector, if any.
     */
    public function pseudo(): ?string
    {
        return $this->pseudo;
    }

    /**
     * Returns the pseudo-selector argument, if any.
     */
    public function pseudoArgument(): ?int
    {
        return $this->pseudoArgument;
    }
}
