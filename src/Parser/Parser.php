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
     * Parses input with the provided grammar.
     */
    public function parse(Grammar $grammar, string $input, ?string $startRule = null): ParseResult
    {
        $ruleName = $startRule ?? $grammar->startRule();
        $context = $grammar->contextFor($input);

        try {
            $result = $context->matchRule($ruleName, 0);
        } catch (LeftRecursionException $exception) {
            $position = $context->input()->lineAndColumn($exception->offset());

            return ParseResult::failure(
                $exception->offset(),
                substr($input, 0, $exception->offset()),
                ParseError::leftRecursion(
                    $exception->ruleName(),
                    $exception->offset(),
                    $position['line'],
                    $position['column'],
                    $context->input()->snippet($exception->offset()),
                ),
            );
        }

        if ($result === null || $result->endOffset() !== strlen($input)) {
            return ParseResult::failure(
                $result?->endOffset() ?? 0,
                $result === null ? '' : substr($input, 0, $result->endOffset()),
                $context->error(),
            );
        }

        $nodes = $result->nodes();

        return ParseResult::success($result->endOffset(), substr($input, 0, $result->endOffset()), $nodes[0]);
    }
}
