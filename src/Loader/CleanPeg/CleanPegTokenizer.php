<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Loader\CleanPeg;

use EmanueleCoppola\PHPeg\Error\GrammarSyntaxError;

/**
 * Tokenizes CleanPeg grammar source text.
 */
class CleanPegTokenizer
{
    private int $offset = 0;

    private int $line = 1;

    private int $column = 1;

    private int $length;

    public function __construct(
        private readonly string $source,
    ) {
        $this->length = strlen($source);
    }

    /**
     * @return list<CleanPegToken>
     */
    public function tokenize(): array
    {
        $tokens = [];

        while ($this->offset < $this->length) {
            $char = $this->source[$this->offset];

            if ($char === ' ' || $char === "\t" || $char === "\r") {
                $this->advance();
                continue;
            }

            if ($char === "\n") {
                $tokens[] = new CleanPegToken('NEWLINE', "\n", $this->line, $this->column);
                $this->advanceLine();
                continue;
            }

            if ($char === '#') {
                while ($this->offset < $this->length && $this->source[$this->offset] !== "\n") {
                    $this->advance();
                }
                continue;
            }

            $line = $this->line;
            $column = $this->column;

            if ($char === '=' || $char === '/' || $char === '?' || $char === '*' || $char === '+' || $char === '(' || $char === ')') {
                $this->advance();
                $tokens[] = new CleanPegToken([
                    '=' => 'EQUAL',
                    '/' => 'SLASH',
                    '?' => 'QUESTION',
                    '*' => 'STAR',
                    '+' => 'PLUS',
                    '(' => 'LPAREN',
                    ')' => 'RPAREN',
                ][$char], $char, $line, $column);
                continue;
            }

            if ($char === '"') {
                $tokens[] = new CleanPegToken('STRING', $this->readString(), $line, $column);
                continue;
            }

            if ($char === 'r' && $this->offset + 1 < $this->length && ($this->source[$this->offset + 1] === '\'' || $this->source[$this->offset + 1] === '"')) {
                $tokens[] = new CleanPegToken('REGEX', $this->readRegex(), $line, $column);
                continue;
            }

            if (preg_match('/[A-Za-z_]/A', $char) === 1) {
                $tokens[] = new CleanPegToken('IDENT', $this->readIdentifier(), $line, $column);
                continue;
            }

            throw new GrammarSyntaxError('CleanPeg', $line, $column, sprintf('unexpected character "%s"', $char));
        }

        $tokens[] = new CleanPegToken('EOF', '', $this->line, $this->column);

        return $tokens;
    }

    private function readIdentifier(): string
    {
        $start = $this->offset;
        $this->advance();

        while ($this->offset < $this->length && preg_match('/[A-Za-z0-9_]/A', $this->source[$this->offset]) === 1) {
            $this->advance();
        }

        return substr($this->source, $start, $this->offset - $start);
    }

    private function readString(): string
    {
        $this->advance();
        $value = '';

        while ($this->offset < $this->length) {
            $char = $this->source[$this->offset];

            if ($char === '\\') {
                if ($this->offset + 1 >= $this->length) {
                    throw new GrammarSyntaxError('CleanPeg', $this->line, $this->column, 'unclosed string literal');
                }

                $value .= '\\' . $this->source[$this->offset + 1];
                $this->advance();
                $this->advance();
                continue;
            }

            if ($char === '"') {
                $this->advance();

                return stripcslashes($value);
            }

            if ($char === "\n") {
                throw new GrammarSyntaxError('CleanPeg', $this->line, $this->column, 'unclosed string literal');
            }

            $value .= $char;
            $this->advance();
        }

        throw new GrammarSyntaxError('CleanPeg', $this->line, $this->column, 'unclosed string literal');
    }

    private function readRegex(): string
    {
        $this->advance();
        $quote = $this->source[$this->offset];
        $this->advance();
        $value = '';

        while ($this->offset < $this->length) {
            $char = $this->source[$this->offset];

            if ($char === '\\') {
                if ($this->offset + 1 >= $this->length) {
                    throw new GrammarSyntaxError('CleanPeg', $this->line, $this->column, 'unclosed regex literal');
                }

                $value .= '\\' . $this->source[$this->offset + 1];
                $this->advance();
                $this->advance();
                continue;
            }

            if ($char === $quote) {
                $this->advance();

                return $value;
            }

            if ($char === "\n") {
                throw new GrammarSyntaxError('CleanPeg', $this->line, $this->column, 'unclosed regex literal');
            }

            $value .= $char;
            $this->advance();
        }

        throw new GrammarSyntaxError('CleanPeg', $this->line, $this->column, 'unclosed regex literal');
    }

    private function advance(): void
    {
        $this->offset++;
        $this->column++;
    }

    private function advanceLine(): void
    {
        $this->offset++;
        $this->line++;
        $this->column = 1;
    }
}
