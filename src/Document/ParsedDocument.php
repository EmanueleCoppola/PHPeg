<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Document;

use EmanueleCoppola\PHPPeg\Ast\AstNode;
use EmanueleCoppola\PHPPeg\Ast\AstNodeCollection;
use EmanueleCoppola\PHPPeg\Ast\AstSelectorParser;
use EmanueleCoppola\PHPPeg\Ast\AstSelectorStep;
use EmanueleCoppola\PHPPeg\Grammar\Grammar;
use EmanueleCoppola\PHPPeg\Printer\PrintPolicy;
use EmanueleCoppola\PHPPeg\Printer\SourcePreservingPrinter;
use EmanueleCoppola\PHPPeg\Result\ParseResult;

/**
 * Editable parsed document rooted at a source-aware AST.
 */
class ParsedDocument
{
    private bool $modified = false;

    public function __construct(
        private readonly Grammar $grammar,
        private readonly string $source,
        private readonly AstNode $root,
    ) {
        $this->root->attachDocument($this);
    }

    public function root(): AstNode
    {
        return $this->root;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function isModified(): bool
    {
        return $this->modified;
    }

    public function markModified(): void
    {
        $this->modified = true;
    }

    public function query(string $selector, ?AstNode $scope = null): AstNodeCollection
    {
        $selectorAst = AstSelectorParser::parse($selector);
        $current = [$scope ?? $this->root];

        foreach ($selectorAst->steps() as $index => $step) {
            $next = [];

            foreach ($current as $node) {
                $candidates = $step->combinator() === 'child' && $index > 0
                    ? $node->directChildren()
                    : $node->descendantsAndSelf();

                foreach ($candidates as $candidate) {
                    if ($this->matchesStep($candidate, $step)) {
                        $next[] = $candidate;
                    }
                }
            }

            $current = $this->applyPseudo($this->deduplicate($next), $step);
        }

        return new AstNodeCollection($current, $this);
    }

    public function print(?PrintPolicy $policy = null): string
    {
        return (new SourcePreservingPrinter($policy ?? new PrintPolicy()))->print($this->root);
    }

    public function validatePrintedSource(): ParseResult
    {
        return $this->grammar->parse($this->print(), $this->root->name());
    }

    public function validate(): ParseResult
    {
        return $this->validatePrintedSource();
    }

    private function matchesStep(AstNode $node, AstSelectorStep $step): bool
    {
        if ($node->name() !== $step->name()) {
            return false;
        }

        foreach ($step->attributes() as $name => $value) {
            if ($node->attribute($name) !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<AstNode> $nodes
     * @return list<AstNode>
     */
    private function deduplicate(array $nodes): array
    {
        $seen = [];
        $result = [];

        foreach ($nodes as $node) {
            $key = spl_object_id($node);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $result[] = $node;
        }

        return $result;
    }

    /**
     * @param list<AstNode> $nodes
     * @return list<AstNode>
     */
    private function applyPseudo(array $nodes, AstSelectorStep $step): array
    {
        return match ($step->pseudo()) {
            null => $nodes,
            'first' => $this->applyGroupedPseudo($nodes, static fn (array $group): array => $group === [] ? [] : [$group[0]]),
            'last' => $this->applyGroupedPseudo($nodes, static fn (array $group): array => $group === [] ? [] : [$group[array_key_last($group)]]),
            'nth-child' => $this->applyGroupedPseudo($nodes, fn (array $group): array => isset($group[$step->pseudoArgument() - 1]) ? [$group[$step->pseudoArgument() - 1]] : []),
            default => $nodes,
        };
    }

    /**
     * @param list<AstNode> $nodes
     * @return list<AstNode>
     */
    private function applyGroupedPseudo(array $nodes, callable $selector): array
    {
        $groups = [];

        foreach ($nodes as $node) {
            $parentId = $node->parent() === null ? 'root' : (string) spl_object_id($node->parent());
            $groups[$parentId][] = $node;
        }

        $result = [];
        foreach ($groups as $group) {
            array_push($result, ...$selector($group));
        }

        return $result;
    }
}
