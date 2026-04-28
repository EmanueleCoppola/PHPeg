<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Ast;

use EmanueleCoppola\PHPPeg\Document\ParsedDocument;

/**
 * Represents query results over AST nodes.
 */
class AstNodeCollection
{
    /**
     * @param list<AstNode> $nodes
     */
    public function __construct(
        private readonly array $nodes,
        private readonly ?ParsedDocument $document = null,
    ) {
    }

    public function count(): int
    {
        return count($this->nodes);
    }

    public function isEmpty(): bool
    {
        return $this->nodes === [];
    }

    public function first(): ?AstNode
    {
        return $this->nodes[0] ?? null;
    }

    public function last(): ?AstNode
    {
        return $this->nodes === [] ? null : $this->nodes[array_key_last($this->nodes)];
    }

    /**
     * @return list<AstNode>
     */
    public function all(): array
    {
        return $this->nodes;
    }

    /**
     * Applies a callback to each node.
     */
    public function each(callable $callback): self
    {
        foreach ($this->nodes as $node) {
            $callback($node);
        }

        return $this;
    }

    public function appendNode(AstNode $node): self
    {
        foreach ($this->nodes as $target) {
            $target->appendNode(clone $node);
        }

        return $this;
    }

    public function prependNode(AstNode $node): self
    {
        foreach ($this->nodes as $target) {
            $target->prependNode(clone $node);
        }

        return $this;
    }
}
