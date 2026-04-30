<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Printer;

use EmanueleCoppola\PHPeg\Ast\AstNode;
use EmanueleCoppola\PHPeg\Error\PrintError;

/**
 * Prints ASTs while preserving original source slices for unchanged nodes.
 */
class SourcePreservingPrinter
{
    /**
     * Initializes a new SourcePreservingPrinter instance.
     */
    public function __construct(
        private readonly PrintPolicy $policy = new PrintPolicy(),
    ) {
    }

    /**
     * Renders the node with source preservation.
     */
    public function print(AstNode $node): string
    {
        if (!$node->isModified() && $node->isOriginal() && !$node->isRemoved()) {
            return $node->originalText();
        }

        if ($node->isInserted()) {
            $text = $node->text();
            if ($text !== '') {
                return $text;
            }

            if ($node->children() === []) {
                throw new PrintError(sprintf('Inserted node "%s" requires explicit render text.', $node->name()));
            }

            return implode('', array_map($this->print(...), $node->children()));
        }

        if ($node->originalChildren() === []) {
            return $this->renderLeafMutation($node);
        }

        return $this->renderOriginalNodeWithMutations($node);
    }

    /**
     * Renders a mutated leaf node.
     */
    private function renderLeafMutation(AstNode $node): string
    {
        $text = $node->text();
        if ($text !== '') {
            return $text;
        }

        return $node->originalText();
    }

    /**
     * Renders an original node after source-preserving mutations.
     */
    private function renderOriginalNodeWithMutations(AstNode $node): string
    {
        $originalChildren = $node->originalChildren();

        if ($originalChildren === []) {
            return $this->renderContainerWithoutOriginalChildren($node);
        }

        $segments = $this->computeSegments($node, $originalChildren);
        $output = $segments[0];
        $output .= $this->printInsertions($node->insertionsBefore()[0] ?? []);
        $slotNodes = $node->slotNodes();

        foreach ($originalChildren as $index => $child) {
            $currentChild = $slotNodes[$index] ?? null;
            if ($currentChild !== null && !$currentChild->isRemoved()) {
                $output .= $this->print($currentChild);
            }

            $output .= $this->printInsertions($node->insertionsAfter()[$index] ?? []);

            if ($index + 1 < count($segments)) {
                $output .= $segments[$index + 1];
                $output .= $this->printInsertions($node->insertionsBefore()[$index + 1] ?? []);
            }
        }

        return $output;
    }

    /**
     * @param list<AstNode> $insertions
     */
    private function printInsertions(array $insertions): string
    {
        $output = '';

        foreach ($insertions as $child) {
            if ($child->isRemoved()) {
                continue;
            }

            $output .= $this->print($child);
        }

        return $output;
    }

    /**
     * @param list<AstNode> $originalChildren
     * @return list<string>
     */
    private function computeSegments(AstNode $node, array $originalChildren): array
    {
        $segments = [];
        $cursor = $node->startOffset();
        $source = $node->originalText();

        foreach ($originalChildren as $child) {
            $segments[] = substr($source, $cursor - $node->startOffset(), $child->startOffset() - $cursor);
            $cursor = $child->endOffset();
        }

        $segments[] = substr($source, $cursor - $node->startOffset());

        return $segments;
    }

    /**
     * Renders a mutated container node without original children.
     */
    private function renderContainerWithoutOriginalChildren(AstNode $node): string
    {
        $source = $node->originalText();
        $insertions = $node->insertionsAfter()[-1] ?? [];
        if ($insertions === []) {
            return $source;
        }

        $closeBrace = strrpos($source, '}');
        if ($closeBrace === false) {
            return $source . $this->printInsertions($insertions);
        }

        return substr($source, 0, $closeBrace)
            . $this->printInsertions($insertions)
            . substr($source, $closeBrace);
    }
}
