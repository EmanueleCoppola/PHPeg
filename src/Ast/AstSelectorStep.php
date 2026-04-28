<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Ast;

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

    public function name(): string
    {
        return $this->name;
    }

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

    public function pseudo(): ?string
    {
        return $this->pseudo;
    }

    public function pseudoArgument(): ?int
    {
        return $this->pseudoArgument;
    }
}
