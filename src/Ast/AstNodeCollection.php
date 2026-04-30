<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Ast;

use EmanueleCoppola\PHPeg\Document\ParsedDocument;

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

    /**
     * Returns the number of items in the collection.
     */
    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * Returns whether the collection is empty.
     */
    public function isEmpty(): bool
    {
        return $this->nodes === [];
    }

    /**
     * Returns the first item, or null when the collection is empty.
     */
    public function first(): ?AstNode
    {
        return $this->nodes[0] ?? null;
    }

    /**
     * Returns the last item, or null when the collection is empty.
     */
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

    /**
     * Appends a cloned node to each selected node.
     */
    public function appendNode(AstNode $node): self
    {
        foreach ($this->nodes as $target) {
            $target->appendNode(clone $node);
        }

        return $this;
    }

    /**
     * Prepends a cloned node to each selected node.
     */
    public function prependNode(AstNode $node): self
    {
        foreach ($this->nodes as $target) {
            $target->prependNode(clone $node);
        }

        return $this;
    }
}
