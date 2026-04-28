<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Ast;

use EmanueleCoppola\PHPPeg\Document\ParsedDocument;
use EmanueleCoppola\PHPPeg\Error\AstMutationError;
use EmanueleCoppola\PHPPeg\Mutation\InsertPosition;

/**
 * Mutable source-aware AST node used for querying and source-preserving edits.
 */
class AstNode
{
    /**
     * @var list<AstNode>
     */
    private array $children;

    /**
     * @var list<AstNode>
     */
    private array $originalChildren;

    /**
     * @var array<int, AstNode|null>
     */
    private array $slotNodes = [];

    private ?AstNode $parent = null;

    private ?ParsedDocument $document = null;

    /**
     * @var array<string, string>
     */
    private array $attributes;

    private bool $modified = false;

    private bool $inserted = false;

    private bool $removed = false;

    private string $originalText;

    private ?string $renderText;

    /**
     * @var array<int, list<AstNode>>
     */
    private array $insertBefore = [];

    /**
     * @var array<int, list<AstNode>>
     */
    private array $insertAfter = [];

    public function __clone()
    {
        $this->parent = null;
        $this->document = null;
        $clonedChildren = [];

        foreach ($this->children as $child) {
            $clonedChild = clone $child;
            $clonedChild->parent = $this;
            $clonedChildren[] = $clonedChild;
        }

        $this->children = $clonedChildren;
        $this->originalChildren = $clonedChildren;
        $this->slotNodes = [];
        foreach ($clonedChildren as $index => $child) {
            $this->slotNodes[$index] = $child;
        }
        $this->insertBefore = [];
        $this->insertAfter = [];
        $this->inserted = true;
        $this->removed = false;
    }

    /**
     * @param list<AstNode> $children
     * @param array<string, string> $attributes
     */
    public function __construct(
        private readonly string $name,
        string $text,
        private readonly int $startOffset,
        private readonly int $endOffset,
        array $children = [],
        array $attributes = [],
        bool $isOriginal = true,
        ?string $renderText = null,
    ) {
        $this->children = array_values($children);
        $this->originalChildren = array_values($children);
        $this->attributes = $attributes;
        $this->originalText = $text;
        $this->renderText = $renderText ?? (!$isOriginal ? $text : null);
        $this->inserted = !$isOriginal;

        foreach ($this->children as $child) {
            $child->parent = $this;
        }

        foreach ($this->originalChildren as $index => $child) {
            $this->slotNodes[$index] = $child;
        }
    }

    /**
     * Returns the rule name that created this node.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the exact original or rendered text for this node.
     */
    public function text(): string
    {
        if ($this->renderText !== null) {
            return $this->renderText;
        }

        return $this->originalText;
    }

    /**
     * Returns the original start offset in the source input.
     */
    public function startOffset(): int
    {
        return $this->startOffset;
    }

    /**
     * Returns the original end offset in the source input.
     */
    public function endOffset(): int
    {
        return $this->endOffset;
    }

    /**
     * @return list<AstNode>
     */
    public function children(): array
    {
        return array_values(array_filter($this->children, static fn (AstNode $child): bool => !$child->removed));
    }

    /**
     * Returns the parent node, or null for the root node.
     */
    public function parent(): ?AstNode
    {
        return $this->parent;
    }

    /**
     * Returns whether this node comes from the original parsed source.
     */
    public function isOriginal(): bool
    {
        return !$this->inserted;
    }

    /**
     * Returns whether this node or its child list has been modified.
     */
    public function isModified(): bool
    {
        return $this->modified;
    }

    /**
     * Returns whether this node was inserted after parsing.
     */
    public function isInserted(): bool
    {
        return $this->inserted;
    }

    /**
     * Returns whether this node has been removed from the tree.
     */
    public function isRemoved(): bool
    {
        return $this->removed;
    }

    /**
     * Returns the owning parsed document when attached.
     */
    public function document(): ?ParsedDocument
    {
        return $this->document;
    }

    /**
     * Returns the first child with the provided rule name, or null.
     */
    public function firstChild(string $name): ?AstNode
    {
        foreach ($this->children() as $child) {
            if ($child->name() === $name) {
                return $child;
            }
        }

        return null;
    }

    /**
     * @return list<AstNode>
     */
    public function childrenByName(string $name): array
    {
        return array_values(
            array_filter(
                $this->children(),
                static fn (AstNode $child): bool => $child->name() === $name,
            ),
        );
    }

    /**
     * Queries descendants rooted at this node using the selector API.
     */
    public function query(string $selector): AstNodeCollection
    {
        if ($this->document === null) {
            throw new AstMutationError('Cannot query a detached AST node.');
        }

        return $this->document->query($selector, $this);
    }

    /**
     * Returns a semantic attribute value when available.
     */
    public function attribute(string $name): ?string
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return match ($name) {
            'text' => trim($this->text()),
            'type' => $this->name(),
            'name' => $this->derivedNameAttribute(),
            'value' => $this->derivedValueAttribute(),
            default => null,
        };
    }

    /**
     * Adds a node as the first logical child.
     */
    public function prependNode(AstNode $node): self
    {
        return $this->insertChild($node, InsertPosition::Prepend);
    }

    /**
     * Adds a node as the last logical child.
     */
    public function appendNode(AstNode $node): self
    {
        return $this->insertChild($node, InsertPosition::Append);
    }

    /**
     * Inserts a node before this node.
     */
    public function before(AstNode $node): self
    {
        $parent = $this->parent ?? throw new AstMutationError('Cannot insert before the root node.');
        $parent->insertRelativeToChild($this, $node, InsertPosition::Before);

        return $this;
    }

    /**
     * Inserts a node after this node.
     */
    public function after(AstNode $node): self
    {
        $parent = $this->parent ?? throw new AstMutationError('Cannot insert after the root node.');
        $parent->insertRelativeToChild($this, $node, InsertPosition::After);

        return $this;
    }

    /**
     * Replaces this node with another node.
     */
    public function replaceWith(AstNode $node): self
    {
        $parent = $this->parent ?? throw new AstMutationError('Cannot replace the root node.');
        $parent->replaceChild($this, $node);

        return $this;
    }

    /**
     * Removes this node from the tree.
     */
    public function remove(): self
    {
        $parent = $this->parent ?? throw new AstMutationError('Cannot remove the root node.');
        $parent->removeChild($this);

        return $this;
    }

    /**
     * Returns whether the node can accept appended/prepended children.
     */
    public function canContainChildren(): bool
    {
        if ($this->removed) {
            return false;
        }

        if ($this->children !== [] || $this->originalChildren !== []) {
            return true;
        }

        return str_contains($this->originalText, '{') && str_contains($this->originalText, '}');
    }

    /**
     * Attaches this node and its descendants to a parsed document.
     */
    public function attachDocument(ParsedDocument $document): void
    {
        $this->document = $document;

        foreach ($this->children as $child) {
            $child->parent = $this;
            $child->attachDocument($document);
        }
    }

    /**
     * Returns the original text slice captured during parsing.
     */
    public function originalText(): string
    {
        return $this->originalText;
    }

    /**
     * @return list<AstNode>
     */
    public function originalChildren(): array
    {
        return $this->originalChildren;
    }

    /**
     * @return array<int, AstNode|null>
     */
    public function slotNodes(): array
    {
        return $this->slotNodes;
    }

    /**
     * @return array<int, list<AstNode>>
     */
    public function insertionsBefore(): array
    {
        return $this->insertBefore;
    }

    /**
     * @return array<int, list<AstNode>>
     */
    public function insertionsAfter(): array
    {
        return $this->insertAfter;
    }

    /**
     * Stores an explicit renderable text representation for inserted/replaced nodes.
     */
    public function setRenderText(string $text): void
    {
        $this->renderText = $text;
        $this->markModified();
    }

    /**
     * @param array<string, string> $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
        $this->markModified();
    }

    /**
     * @return list<AstNode>
     */
    public function descendantsAndSelf(): array
    {
        $nodes = [$this];

        foreach ($this->children() as $child) {
            array_push($nodes, ...$child->descendantsAndSelf());
        }

        return $nodes;
    }

    /**
     * @return list<AstNode>
     */
    public function directChildren(): array
    {
        return $this->children();
    }

    private function insertChild(AstNode $node, InsertPosition $position): self
    {
        if (!$this->canContainChildren()) {
            throw new AstMutationError(sprintf('Cannot insert children into leaf node "%s".', $this->name));
        }

        $node->inserted = true;
        $node->parent = $this;
        if ($this->document !== null) {
            $node->attachDocument($this->document);
        }

        $this->children = match ($position) {
            InsertPosition::Prepend => [...[$node], ...$this->children()],
            InsertPosition::Append => [...$this->children(), $node],
            default => $this->children,
        };

        if ($this->originalChildren === []) {
            $this->insertAfter[-1] ??= [];
            $this->insertAfter[-1][] = $node;
        } elseif ($position === InsertPosition::Prepend) {
            $this->insertBefore[0] ??= [];
            $this->insertBefore[0][] = $node;
        } else {
            $lastSlot = count($this->originalChildren) - 1;
            $this->insertAfter[$lastSlot] ??= [];
            $this->insertAfter[$lastSlot][] = $node;
        }

        $this->markModified();

        return $this;
    }

    private function insertRelativeToChild(AstNode $target, AstNode $node, InsertPosition $position): void
    {
        $index = $this->findChildIndex($target);

        $node->inserted = true;
        $node->parent = $this;
        if ($this->document !== null) {
            $node->attachDocument($this->document);
        }

        array_splice($this->children, $position === InsertPosition::Before ? $index : $index + 1, 0, [$node]);

        $originalIndex = $this->findOriginalChildIndex($target);
        if ($originalIndex === null) {
            $originalIndex = max(0, $index - 1);
        }

        if ($position === InsertPosition::Before) {
            $this->insertBefore[$originalIndex] ??= [];
            $this->insertBefore[$originalIndex][] = $node;
        } else {
            $this->insertAfter[$originalIndex] ??= [];
            $this->insertAfter[$originalIndex][] = $node;
        }

        $this->markModified();
    }

    private function replaceChild(AstNode $target, AstNode $replacement): void
    {
        $index = $this->findChildIndex($target);
        $slotIndex = $this->findOriginalChildIndex($target);

        $replacement->inserted = true;
        $replacement->parent = $this;
        if ($this->document !== null) {
            $replacement->attachDocument($this->document);
        }

        $this->children[$index] = $replacement;
        $target->removed = true;
        $target->parent = null;

        if ($slotIndex !== null) {
            $this->slotNodes[$slotIndex] = $replacement;
        }

        $this->markModified();
    }

    private function removeChild(AstNode $target): void
    {
        $index = $this->findChildIndex($target);
        array_splice($this->children, $index, 1);
        $target->removed = true;
        $target->parent = null;
        if (($slotIndex = $this->findOriginalChildIndex($target)) !== null) {
            $this->slotNodes[$slotIndex] = null;
        }
        $this->markModified();
    }

    private function findChildIndex(AstNode $target): int
    {
        foreach ($this->children as $index => $child) {
            if ($child === $target) {
                return $index;
            }
        }

        throw new AstMutationError('Target node is not a child of the expected parent.');
    }

    private function findOriginalChildIndex(AstNode $target): ?int
    {
        foreach ($this->originalChildren as $index => $child) {
            if ($child === $target) {
                return $index;
            }
        }

        return null;
    }

    private function markModified(): void
    {
        $this->modified = true;
        if ($this->document !== null) {
            $this->document->markModified();
        }
        if ($this->parent !== null && !$this->parent->modified) {
            $this->parent->markModified();
        }
    }

    private function derivedNameAttribute(): ?string
    {
        foreach (['Identifier', 'Name', 'Key'] as $childName) {
            $child = $this->firstChild($childName);
            if ($child !== null) {
                return trim($child->text(), "\"' \t\r\n");
            }
        }

        return null;
    }

    private function derivedValueAttribute(): ?string
    {
        foreach (['Value', 'String', 'Number', 'Literal', 'Path', 'Url', 'ValueList'] as $childName) {
            $child = $this->firstChild($childName);
            if ($child !== null) {
                return trim($child->text(), "\"' \t\r\n");
            }
        }

        return null;
    }
}
