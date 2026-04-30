<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Expression;

use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Matches a lake node that consumes water until the compiled stop set is reached.
 */
class LakeExpression extends AbstractExpression
{
    /**
     * Creates a lake expression.
     */
    public function __construct(
        private readonly ?string $name = null,
        private readonly bool $capture = true,
    ) {
    }

    /**
     * Returns the optional node name assigned to the lake.
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * Returns whether the lake should produce an AST node.
     */
    public function capture(): bool
    {
        return $this->capture;
    }

    /**
     * Matches water up to the next valid stop point.
     */
    /**
     * @inheritDoc
     */
    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        return $context->matchLakeExpression($this, $offset);
    }

    /**
     * Returns a short human-readable description.
     */
    public function describe(): string
    {
        return $this->name === null ? '~' : sprintf('<%s>', $this->name);
    }
}
