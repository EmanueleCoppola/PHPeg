<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Ast;

/**
 * Creates generic AST nodes for insertions and replacements.
 */
class AstNodeFactory
{
    /**
     * @param list<AstNode> $children
     * @param array<string, string> $attributes
     */
    public function node(string $name, array $children = [], ?string $text = null, array $attributes = []): AstNode
    {
        return new AstNode($name, $text ?? '', -1, -1, $children, $attributes, false, $text);
    }

    /**
     * Creates a leaf-like node with explicit source text.
     */
    public function token(string $name, string $text): AstNode
    {
        return new AstNode($name, $text, -1, -1, [], [], false, $text);
    }
}
