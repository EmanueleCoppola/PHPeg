<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Parser;

use EmanueleCoppola\PHPeg\Error\LeftRecursionException;
use EmanueleCoppola\PHPeg\Error\ParseError;
use EmanueleCoppola\PHPeg\Grammar\Grammar;
use EmanueleCoppola\PHPeg\Result\ParseResult;

/**
 * Executes a grammar against input text.
 */
class Parser
{
    /**
     * Creates a parser with the provided options.
     */
    public function __construct(
        private readonly ParserOptions $options = new ParserOptions(),
    ) {
    }

    /**
     * Returns a copy of the parser with updated options.
     */
    public function withOptions(ParserOptions $options): self
    {
        return new self($options);
    }

    /**
     * Returns the parser options.
     */
    public function options(): ParserOptions
    {
        return $this->options;
    }

    /**
     * Parses input with the provided grammar.
     */
    public function parse(Grammar $grammar, string $input, ?string $startRule = null, ?ParserOptions $options = null): ParseResult
    {
        $ruleName = $startRule ?? $grammar->startRule();
        $context = $grammar->contextFor($input, $options ?? $this->options);
        $inputBuffer = $context->input();

        try {
            $result = $context->matchRule($ruleName, 0);
        } catch (LeftRecursionException $exception) {
            $position = $inputBuffer->lineAndColumn($exception->offset());

            return ParseResult::failure(
                $exception->offset(),
                $inputBuffer->slice(0, $exception->offset()),
                ParseError::leftRecursion(
                    $exception->ruleName(),
                    $exception->offset(),
                    $position['line'],
                    $position['column'],
                    $inputBuffer->snippet($exception->offset()),
                ),
            );
        }

        if ($result === null || $result->endOffset() !== $inputBuffer->length()) {
            return ParseResult::failure(
                $result?->endOffset() ?? 0,
                $result === null ? '' : $inputBuffer->slice(0, $result->endOffset()),
                $context->error(),
            );
        }

        $nodes = $result->nodes();

        return ParseResult::success($result->endOffset(), $inputBuffer->slice(0, $result->endOffset()), $nodes[0]);
    }
}
