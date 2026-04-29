<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Result;

use EmanueleCoppola\PHPeg\Ast\AstNode;
use EmanueleCoppola\PHPeg\Error\ParseError;

/**
 * Public parse result object exposed to consumers.
 */
class ParseResult
{
    private function __construct(
        private readonly bool $success,
        private readonly int $finalOffset,
        private readonly string $matchedText,
        private readonly ?AstNode $node,
        private readonly ?ParseError $error,
    ) {
    }

    /**
     * Creates a successful parse result.
     */
    public static function success(int $finalOffset, string $matchedText, AstNode $node): self
    {
        return new self(true, $finalOffset, $matchedText, $node, null);
    }

    /**
     * Creates a failed parse result.
     */
    public static function failure(int $finalOffset, string $matchedText, ParseError $error): self
    {
        return new self(false, $finalOffset, $matchedText, null, $error);
    }

    /**
     * Indicates whether parsing succeeded.
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Returns the root AST node on success.
     */
    public function node(): ?AstNode
    {
        return $this->node;
    }

    /**
     * Returns the parse error on failure.
     */
    public function error(): ?ParseError
    {
        return $this->error;
    }

    /**
     * Returns the matched input segment.
     */
    public function matchedText(): string
    {
        return $this->matchedText;
    }

    /**
     * Returns the final parser offset.
     */
    public function finalOffset(): int
    {
        return $this->finalOffset;
    }
}
