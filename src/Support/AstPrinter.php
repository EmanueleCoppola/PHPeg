<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Support;

use EmanueleCoppola\PHPeg\Ast\AstNode;

/**
 * Builds a readable tree representation for AST nodes.
 */
class AstPrinter
{
    /**
     * Renders an AST tree into a readable multiline string.
     */
    public static function print(AstNode $node): string
    {
        $lines = [$node->name()];
        $children = self::displayChildren($node);

        foreach ($children as $index => $child) {
            $isLast = $index === array_key_last($children);
            self::appendNodeLines($lines, $child, '', $isLast);
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * @param list<string> $lines
     */
    private static function appendNodeLines(array &$lines, AstNode $node, string $prefix, bool $isLast): void
    {
        $lines[] = $prefix . ($isLast ? '└── ' : '├── ') . self::label($node);
        $children = self::displayChildren($node);
        $childPrefix = $prefix . ($isLast ? '    ' : '│   ');

        foreach ($children as $index => $child) {
            self::appendNodeLines($lines, $child, $childPrefix, $index === array_key_last($children));
        }
    }

    /**
     * @return list<AstNode>
     */
    private static function displayChildren(AstNode $node): array
    {
        return array_values(
            array_filter(
                $node->children(),
                static fn (AstNode $child): bool => $child->children() !== [] || trim($child->text()) !== '',
            ),
        );
    }

    /**
     * Formats a node label for the tree view.
     */
    private static function label(AstNode $node): string
    {
        $name = $node->name();
        $text = trim($node->text());

        if ($name === 'Identifier') {
            return sprintf('Identifier: %s', $text);
        }

        if ($name === 'PrintStatement') {
            if (preg_match('/"([^"]*)"/', $node->text(), $matches) === 1) {
                return sprintf('PrintStatement: "%s"', $matches[1]);
            }

            return sprintf('PrintStatement: %s', $text);
        }

        if ($name === 'String') {
            return sprintf('String: %s', $text);
        }

        return $name;
    }
}
