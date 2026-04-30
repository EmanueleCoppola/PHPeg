<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Support;

use EmanueleCoppola\PHPeg\App\Support\AstDotExporter;
use EmanueleCoppola\PHPeg\Ast\AstNode;
use PHPUnit\Framework\TestCase;

class AstDotExporterTest extends TestCase
{
    /**
     * Verifies a small tree is exported as stable DOT with deterministic node ids.
     */
    public function testExportsMinimalTreeAsDot(): void
    {
        $exporter = new AstDotExporter();
        $tree = new AstNode(
            'Root',
            'root',
            0,
            10,
            [
                new AstNode('Left', 'left', 1, 4),
                new AstNode('Right', 'right', 5, 9),
            ],
            ['kind' => 'lake'],
        );

        $dot = $exporter->export($tree);

        self::assertStringStartsWith("digraph ParseTree {\n", $dot);
        self::assertStringContainsString("  rankdir=TB;\n", $dot);
        self::assertStringContainsString("  node [shape=box];\n", $dot);
        self::assertStringContainsString('  n1 [label="Root\\n0..10\\nroot", style="dashed,rounded,filled", color="#2563eb", fillcolor="#dbeafe", fontcolor="#1e3a8a"];', $dot);
        self::assertStringContainsString('  n2 [label="Left\\n1..4\\nleft", style="rounded,filled", color="#16a34a", fillcolor="#dcfce7", fontcolor="#14532d"];', $dot);
        self::assertStringContainsString('  n3 [label="Right\\n5..9\\nright", style="rounded,filled", color="#16a34a", fillcolor="#dcfce7", fontcolor="#14532d"];', $dot);
        self::assertStringContainsString('  subgraph cluster_legend {', $dot);
        self::assertStringContainsString('    label="Legend";', $dot);
        self::assertStringContainsString('    labelloc="t";', $dot);
        self::assertStringContainsString('    labeljust="l";', $dot);
        self::assertStringContainsString('    style="rounded";', $dot);
        self::assertStringContainsString('    color="#0f172a";', $dot);
        self::assertStringContainsString('    bgcolor="#f8fafc";', $dot);
        self::assertStringContainsString('    penwidth=2;', $dot);
        self::assertStringContainsString('    margin=18;', $dot);
        self::assertStringContainsString('    { rank=same; legend_island; legend_lake; legend_leaf; }', $dot);
        self::assertStringContainsString('    legend_island [label="Island", style="rounded,filled", color="#3b82f6", fillcolor="#eff6ff", fontcolor="#1e3a8a"];', $dot);
        self::assertStringContainsString('    legend_lake [label="Lake", style="dashed,rounded,filled", color="#2563eb", fillcolor="#dbeafe", fontcolor="#1e3a8a"];', $dot);
        self::assertStringContainsString('    legend_leaf [label="Leaf", style="rounded,filled", color="#16a34a", fillcolor="#dcfce7", fontcolor="#14532d"];', $dot);
        self::assertStringContainsString('  { rank=same; n1; legend_island; legend_lake; legend_leaf; }', $dot);
        self::assertStringContainsString('  legend_island -> legend_lake [style=invis];', $dot);
        self::assertStringContainsString('  legend_lake -> legend_leaf [style=invis];', $dot);
        self::assertStringContainsString('  n1 -> n2;', $dot);
        self::assertStringContainsString('  n1 -> n3;', $dot);
        self::assertSame($dot, $exporter->export($tree));
    }

    /**
     * Verifies label escaping handles quotes, backslashes, newlines, carriage returns, and tabs.
     */
    public function testEscapesDotLabels(): void
    {
        $exporter = new AstDotExporter();
        $tree = new AstNode(
            'Node',
            "abc\n\"q\"\\s\r\t",
            0,
            5,
        );

        $dot = $exporter->export($tree);

        self::assertStringContainsString('Node\\n0..5\\nabc\\n\\"q\\"\\\\s\\r\\t', $dot);
    }
}
