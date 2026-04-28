<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Loader\Peg;

use InvalidArgumentException;

/**
 * Tokenizes PEG grammar source text.
 */
class PegTokenizer
{
    private int $offset = 0;

    private int $length;

    public function __construct(
        private readonly string $source,
    ) {
        $this->length = strlen($source);
    }

    /**
     * @return list<PegToken>
     */
    public function tokenize(): array
    {
        $tokens = [];

        while (true) {
            $this->skipIgnored();
            if ($this->offset >= $this->length) {
                break;
            }

            $start = $this->offset;

            if ($this->match('<-')) {
                $tokens[] = new PegToken('ARROW', '<-', $start);
                continue;
            }

            $char = $this->source[$this->offset];

            if (isset([
                '/' => 'SLASH',
                '*' => 'STAR',
                '+' => 'PLUS',
                '?' => 'QUESTION',
                '(' => 'LPAREN',
                ')' => 'RPAREN',
                '.' => 'DOT',
                '&' => 'AND',
                '!' => 'NOT',
            ][$char])) {
                $this->offset++;
                $tokens[] = new PegToken([
                    '/' => 'SLASH',
                    '*' => 'STAR',
                    '+' => 'PLUS',
                    '?' => 'QUESTION',
                    '(' => 'LPAREN',
                    ')' => 'RPAREN',
                    '.' => 'DOT',
                    '&' => 'AND',
                    '!' => 'NOT',
                ][$char], $char, $start);
                continue;
            }

            if ($char === '"' || $char === "'") {
                $tokens[] = new PegToken('LITERAL', $this->readString($char), $start);
                continue;
            }

            if ($char === '[') {
                $tokens[] = new PegToken('CHAR_CLASS', $this->readCharClass(), $start);
                continue;
            }

            if (preg_match('/[A-Za-z_]/A', $char) === 1) {
                $tokens[] = new PegToken('IDENT', $this->readIdentifier(), $start);
                continue;
            }

            throw new InvalidArgumentException(sprintf('Unexpected character "%s" at offset %d.', $char, $this->offset));
        }

        $tokens[] = new PegToken('EOF', '', $this->offset);

        return $tokens;
    }

    private function skipIgnored(): void
    {
        while ($this->offset < $this->length) {
            $char = $this->source[$this->offset];
            if (preg_match('/\s/A', $char) === 1) {
                $this->offset++;
                continue;
            }

            if ($this->match('//')) {
                while ($this->offset < $this->length && $this->source[$this->offset] !== "\n") {
                    $this->offset++;
                }
                continue;
            }

            if ($char === '#') {
                while ($this->offset < $this->length && $this->source[$this->offset] !== "\n") {
                    $this->offset++;
                }
                continue;
            }

            break;
        }
    }

    private function match(string $value): bool
    {
        if (substr_compare($this->source, $value, $this->offset, strlen($value)) !== 0) {
            return false;
        }

        $this->offset += strlen($value);

        return true;
    }

    private function readIdentifier(): string
    {
        $start = $this->offset;
        $this->offset++;

        while ($this->offset < $this->length && preg_match('/[A-Za-z0-9_]/A', $this->source[$this->offset]) === 1) {
            $this->offset++;
        }

        return substr($this->source, $start, $this->offset - $start);
    }

    private function readString(string $quote): string
    {
        $this->offset++;
        $value = '';

        while ($this->offset < $this->length) {
            $char = $this->source[$this->offset];
            if ($char === '\\') {
                if ($this->offset + 1 >= $this->length) {
                    throw new InvalidArgumentException('Unterminated escape sequence in string literal.');
                }

                $value .= '\\' . $this->source[$this->offset + 1];
                $this->offset += 2;
                continue;
            }

            if ($char === $quote) {
                $this->offset++;

                return stripcslashes($value);
            }

            $value .= $char;
            $this->offset++;
        }

        throw new InvalidArgumentException('Unterminated string literal in PEG grammar.');
    }

    private function readCharClass(): string
    {
        $start = $this->offset;
        $this->offset++;

        while ($this->offset < $this->length) {
            $char = $this->source[$this->offset];
            if ($char === '\\') {
                $this->offset += 2;
                continue;
            }

            if ($char === ']') {
                $this->offset++;

                return substr($this->source, $start, $this->offset - $start);
            }

            $this->offset++;
        }

        throw new InvalidArgumentException('Unterminated character class in PEG grammar.');
    }
}
