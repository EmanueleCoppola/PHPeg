<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Support;

use EmanueleCoppola\PHPeg\Ast\AstNode;

/**
 * Converts AST nodes into Graphviz DOT output.
 */
class AstDotExporter
{
    /**
     * Exports a tree rooted at the provided node to a DOT document.
     */
    public function export(AstNode $root): string
    {
        $nodeLines = [];
        $edgeLines = [];
        $counter = 0;
        $rootId = $this->walk($root, null, $counter, $nodeLines, $edgeLines);

        $lines = [
            'digraph ParseTree {',
            '  rankdir=TB;',
            '  node [shape=box];',
            '',
            ...$nodeLines,
            '',
            $this->buildLegendLines($rootId),
            '',
            ...$edgeLines,
            '}',
        ];

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * Traverses the tree in deterministic pre-order and collects DOT lines.
     *
     * @param list<string> $nodeLines
     * @param list<string> $edgeLines
     */
    private function walk(AstNode $node, ?string $parentId, int &$counter, array &$nodeLines, array &$edgeLines): string
    {
        $id = 'n' . (++$counter);
        $style = $this->nodeStyle($node);
        $nodeLines[] = sprintf(
            '  %s [label="%s", style="%s", color="%s", fillcolor="%s", fontcolor="%s"];',
            $id,
            $this->buildLabel($node),
            $style['style'],
            $style['color'],
            $style['fillcolor'],
            $style['fontcolor'],
        );

        if ($parentId !== null) {
            $edgeLines[] = sprintf('  %s -> %s;', $parentId, $id);
        }

        foreach ($node->children() as $child) {
            $this->walk($child, $id, $counter, $nodeLines, $edgeLines);
        }

        return $id;
    }

    /**
     * Builds the legend nodes and places them near the root node.
     */
    private function buildLegendLines(string $rootId): string
    {
        return implode(PHP_EOL, [
            '  subgraph cluster_legend {',
            '    label="Legend";',
            '    labelloc="t";',
            '    labeljust="l";',
            '    style="rounded";',
            '    color="#0f172a";',
            '    bgcolor="#f8fafc";',
            '    penwidth=2;',
            '    margin=18;',
            '    { rank=same; legend_island; legend_lake; legend_leaf; }',
            '    legend_island [label="Island", style="rounded,filled", color="#3b82f6", fillcolor="#eff6ff", fontcolor="#1e3a8a"];',
            '    legend_lake [label="Lake", style="dashed,rounded,filled", color="#2563eb", fillcolor="#dbeafe", fontcolor="#1e3a8a"];',
            '    legend_leaf [label="Leaf", style="rounded,filled", color="#16a34a", fillcolor="#dcfce7", fontcolor="#14532d"];',
            '    legend_island -> legend_lake [style=invis];',
            '    legend_lake -> legend_leaf [style=invis];',
            '  }',
            sprintf('  { rank=same; %s; legend_island; legend_lake; legend_leaf; }', $rootId),
        ]);
    }

    /**
     * Builds a DOT label for one AST node.
     */
    private function buildLabel(AstNode $node): string
    {
        $parts = [
            $this->escapeLabelPart($node->name()),
            $this->escapeLabelPart(sprintf('%d..%d', $node->startOffset(), $node->endOffset())),
        ];

        $preview = $this->preview($node->text());
        if ($preview !== null) {
            $parts[] = $this->escapeLabelPart($preview);
        }

        return implode('\n', $parts);
    }

    /**
     * Returns the visual style for a node based on its role in the tree.
     *
     * @return array{style:string,color:string,fillcolor:string,fontcolor:string}
     */
    private function nodeStyle(AstNode $node): array
    {
        if ($node->isLake()) {
            return [
                'style' => 'dashed,rounded,filled',
                'color' => '#2563eb',
                'fillcolor' => '#dbeafe',
                'fontcolor' => '#1e3a8a',
            ];
        }

        if ($node->children() === []) {
            return [
                'style' => 'rounded,filled',
                'color' => '#16a34a',
                'fillcolor' => '#dcfce7',
                'fontcolor' => '#14532d',
            ];
        }

        return [
            'style' => 'rounded,filled',
            'color' => '#3b82f6',
            'fillcolor' => '#eff6ff',
            'fontcolor' => '#1e3a8a',
        ];
    }

    /**
     * Returns a short preview of the node text, or null when the text is empty or blank.
     */
    private function preview(string $text): ?string
    {
        if (trim($text) === '') {
            return null;
        }

        if (strlen($text) > 48) {
            return substr($text, 0, 24) . '...';
        }

        return $text;
    }

    /**
     * Escapes a label fragment for Graphviz DOT.
     */
    private function escapeLabelPart(string $value): string
    {
        return str_replace(
            ["\\", "\"", "\r", "\n", "\t"],
            ["\\\\", "\\\"", "\\r", "\\n", "\\t"],
            $value,
        );
    }
}
