<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Feature;

use EmanueleCoppola\PHPeg\Ast\AstNodeFactory;
use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Covers lake parsing, AST shape, source preservation, and failure behavior.
 */
class LakeNodesTest extends TestCase
{
    /**
     * Verifies island parsing preserves surrounding water and creates lake nodes.
     */
    public function testIslandParsingPreservesWaterAndCreatesLakeNodes(): void
    {
        $grammar = $this->islandGrammar();
        $source = 'before function hello($a, $b) { echo $a; } after';
        $document = $grammar->parseDocument($source);

        self::assertSame($source, $document->print());
        self::assertSame(4, $document->query('Lake[kind="lake"]')->count());

        $firstLake = $document->query('Lake[kind="lake"]:first')->first();
        self::assertNotNull($firstLake);
        self::assertSame('Lake', $firstLake?->name());
        self::assertSame('before ', $firstLake?->text());
        self::assertSame(0, $firstLake?->startOffset());
        self::assertSame(7, $firstLake?->endOffset());
    }

    /**
     * Verifies a named lake produces a named AST node.
     */
    public function testNamedLakeCreatesNamedNode(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->seq($g->literal('{'), $g->lake('BodyWater'), $g->literal('}')))
            ->build();

        $document = $grammar->parseDocument('{abc}');
        $lake = $document->query('BodyWater[kind="lake"]')->first();

        self::assertNotNull($lake);
        self::assertSame('BodyWater', $lake?->name());
        self::assertSame('abc', $lake?->text());
        self::assertSame(1, $document->query('BodyWater[kind="lake"]')->count());
    }

    /**
     * Verifies a named lake can use a local water profile instead of the global fallback.
     */
    public function testNamedLakeUsesLocalWaterProfile(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g
            ->lakeRule('BodyWater', $g->regex('[^{}]+'))
            ->grammar('Program')
            ->rule('Program', $g->seq($g->literal('{'), $g->lake('BodyWater'), $g->literal('}')))
            ->rule('Whitespace', $g->regex('[ \t\r\n]+'), true)
            ->build();

        $document = $grammar->parseDocument('{foo bar}');
        $lake = $document->query('BodyWater[kind="lake"]:first')->first();
        $water = $document->query('BodyWater[kind="water"]:first')->first();

        self::assertNotNull($lake);
        self::assertNotNull($water);
        self::assertSame('foo bar', $lake?->text());
        self::assertSame('foo bar', $water?->text());
        self::assertSame(1, $document->query('BodyWater[kind="water"]')->count());
    }

    /**
     * Verifies a lake before EOF consumes the entire trailing input.
     */
    public function testLakeBeforeEofConsumesToEnd(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->seq($g->lake(), $g->eof()))
            ->build();

        $source = 'trailing text that should be preserved';
        $document = $grammar->parseDocument($source);
        $lake = $document->query('Lake[kind="lake"]')->first();

        self::assertNotNull($lake);
        self::assertSame($source, $lake?->text());
        self::assertSame($source, $document->print());
    }

    /**
     * Verifies missing lake delimiters report a useful parse error.
     */
    public function testMissingDelimiterReportsUsefulError(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->seq($g->literal('{'), $g->lake(), $g->literal('}')))
            ->build();

        $result = $grammar->parse('{abc');

        self::assertFalse($result->isSuccess());
        self::assertStringContainsString('}', $result->error()?->message() ?? '');
    }

    /**
     * Verifies recursive island grammars do not loop forever during lake analysis.
     */
    public function testRecursiveGrammarWithLakeParsesNestedBlocks(): void
    {
        $grammar = $this->recursiveGrammar();
        $source = 'before{inner{deep}more}after';
        $document = $grammar->parseDocument($source);

        self::assertSame($source, $document->print());
        self::assertGreaterThanOrEqual(2, $document->query('Lake[kind="lake"]')->count());
    }

    /**
     * Verifies editing an island preserves surrounding lake text.
     */
    public function testEditingIslandPreservesLakeText(): void
    {
        $grammar = $this->islandGrammar();
        $source = 'before function hello($a, $b) { echo $a; } after';
        $document = $grammar->parseDocument($source);
        $factory = new AstNodeFactory();

        $document->query('Identifier[text="hello"]')->first()?->replaceWith(
            $factory->token('Identifier', 'world'),
        );

        $printed = $document->print();

        self::assertStringContainsString('before ', $printed);
        self::assertStringContainsString('world', $printed);
        self::assertStringContainsString(' after', $printed);
    }

    /**
     * Builds a simple island grammar with top-level and nested lakes.
     */
    private function islandGrammar(): \EmanueleCoppola\PHPeg\Grammar\Grammar
    {
        $g = GrammarBuilder::create();

        return $g->grammar('Program')
            ->rule('Program', $g->seq($g->zeroOrMore($g->choice($g->ref('Function'), $g->lake())), $g->eof()))
            ->rule('Function', $g->seq(
                $g->literal('function'),
                $g->ref('Spacing'),
                $g->ref('Identifier'),
                $g->ref('Spacing'),
                $g->literal('('),
                $g->lake(),
                $g->literal(')'),
                $g->ref('Spacing'),
                $g->ref('Block'),
            ))
            ->rule('Block', $g->seq($g->literal('{'), $g->lake(), $g->literal('}')))
            ->rule('Identifier', $g->oneOrMore($g->charClass('[A-Za-z_]')))
            ->rule('Spacing', $g->zeroOrMore($g->charClass('[ \t\r\n]')))
            ->build();
    }

    /**
     * Builds a recursive island grammar with nested blocks.
     */
    private function recursiveGrammar(): \EmanueleCoppola\PHPeg\Grammar\Grammar
    {
        $g = GrammarBuilder::create();

        return $g->grammar('Program')
            ->rule('Program', $g->seq($g->zeroOrMore($g->choice($g->ref('Block'), $g->lake())), $g->eof()))
            ->rule('Block', $g->seq($g->literal('{'), $g->zeroOrMore($g->choice($g->ref('Block'), $g->lake())), $g->literal('}')))
            ->build();
    }
}
