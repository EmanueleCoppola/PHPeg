<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Support;

use EmanueleCoppola\PHPeg\Ast\AstNode;

/**
 * Converts AST nodes into JSON-serializable arrays.
 */
class AstJsonExporter
{
    /**
     * Exports a list of nodes to arrays suitable for json_encode().
     *
     * @param list<AstNode> $nodes
     * @return list<array<string, mixed>>
     */
    public function exportNodes(array $nodes): array
    {
        $exported = [];

        foreach ($nodes as $node) {
            $exported[] = $this->exportNode($node);
        }

        return $exported;
    }

    /**
     * Exports a single node to a JSON-serializable array.
     *
     * @return array<string, mixed>
     */
    public function exportNode(AstNode $node): array
    {
        return [
            'name' => $node->name(),
            'text' => $node->text(),
            'originalText' => $node->originalText(),
            'startOffset' => $node->startOffset(),
            'endOffset' => $node->endOffset(),
            'isOriginal' => $node->isOriginal(),
            'isModified' => $node->isModified(),
            'isInserted' => $node->isInserted(),
            'isRemoved' => $node->isRemoved(),
            'lake' => $node->isLake(),
            'attributes' => $node->attributes(),
            'semantic' => [
                'text' => $node->attribute('text'),
                'type' => $node->attribute('type'),
                'name' => $node->attribute('name'),
                'value' => $node->attribute('value'),
            ],
            'children' => $this->exportNodes($node->children()),
        ];
    }

    /**
     * Exports a single node using a compact schema.
     *
     * @return array<string, mixed>
     */
    public function exportCompactNode(AstNode $node): array
    {
        return [
            'name' => $node->name(),
            'text' => $node->text(),
            'lake' => $node->isLake(),
            'children' => $this->exportCompactNodes($node->children()),
        ];
    }

    /**
     * Exports a list of nodes using the compact schema.
     *
     * @param list<AstNode> $nodes
     * @return list<array<string, mixed>>
     */
    public function exportCompactNodes(array $nodes): array
    {
        $exported = [];

        foreach ($nodes as $node) {
            $exported[] = $this->exportCompactNode($node);
        }

        return $exported;
    }
}
