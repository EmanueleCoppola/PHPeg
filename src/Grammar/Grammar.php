<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Grammar;

use RuntimeException;
use EmanueleCoppola\PHPPeg\Document\ParsedDocument;
use EmanueleCoppola\PHPPeg\Expression\ExpressionInterface;
use EmanueleCoppola\PHPPeg\Parser\ParseContext;
use EmanueleCoppola\PHPPeg\Parser\InputBuffer;
use EmanueleCoppola\PHPPeg\Parser\Parser;
use EmanueleCoppola\PHPPeg\Result\ParseResult;

/**
 * Immutable PEG grammar container.
 */
class Grammar
{
    /**
     * @param array<string, Rule> $rules
     */
    public function __construct(
        private readonly array $rules,
        private readonly string $startRule,
    ) {
    }

    /**
     * Returns the configured start rule name.
     */
    public function startRule(): string
    {
        return $this->startRule;
    }

    /**
     * Returns a rule by name, or null when missing.
     */
    public function rule(string $name): ?Rule
    {
        return $this->rules[$name] ?? null;
    }

    /**
     * @return array<string, Rule>
     */
    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * Parses input using the configured grammar.
     */
    public function parse(string $input, ?string $startRule = null): ParseResult
    {
        $parser = new Parser();

        return $parser->parse($this, $input, $startRule);
    }

    /**
     * Parses an editable source-preserving document.
     */
    public function parseDocument(string $input, ?string $startRule = null): ParsedDocument
    {
        $result = $this->parse($input, $startRule);
        if (!$result->isSuccess() || $result->node() === null) {
            throw new RuntimeException($result->error()?->message() ?? 'Unable to parse document.');
        }

        return new ParsedDocument($this, $input, $result->node());
    }

    /**
     * Creates a parse context for this grammar and input text.
     */
    public function contextFor(string $input): ParseContext
    {
        return new ParseContext($this, new InputBuffer($input));
    }
}
