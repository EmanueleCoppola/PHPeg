<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Result;

use EmanueleCoppola\PHPeg\Ast\AstNode;

/**
 * Internal immutable expression match result.
 */
class MatchResult
{
    /**
     * @param list<AstNode> $nodes
     */
    public function __construct(
        private readonly int $startOffset,
        private readonly int $endOffset,
        private readonly array $nodes = [],
    ) {
    }

    /**
     * Creates a zero-width successful match.
     */
    public static function empty(int $offset): self
    {
        return new self($offset, $offset, []);
    }

    /**
     * Returns the match start offset.
     */
    public function startOffset(): int
    {
        return $this->startOffset;
    }

    /**
     * Returns the match end offset.
     */
    public function endOffset(): int
    {
        return $this->endOffset;
    }

    /**
     * @return list<AstNode>
     */
    public function nodes(): array
    {
        return $this->nodes;
    }
}
