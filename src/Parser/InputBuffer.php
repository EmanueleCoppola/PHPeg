<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Parser;

/**
 * Provides read-only input access and position calculations.
 */
class InputBuffer
{
    public function __construct(
        private readonly string $input,
    ) {
    }

    /**
     * Returns the full input text.
     */
    public function text(): string
    {
        return $this->input;
    }

    /**
     * Returns the input length in bytes.
     */
    public function length(): int
    {
        return strlen($this->input);
    }

    /**
     * Returns a substring for the provided offset range.
     */
    public function slice(int $startOffset, int $endOffset): string
    {
        return substr($this->input, $startOffset, $endOffset - $startOffset);
    }

    /**
     * Returns the single-byte character at the given offset, or null.
     */
    public function charAt(int $offset): ?string
    {
        if ($offset < 0 || $offset >= $this->length()) {
            return null;
        }

        return $this->input[$offset];
    }

    /**
     * @return array{line:int,column:int}
     */
    public function lineAndColumn(int $offset): array
    {
        $offset = max(0, min($offset, $this->length()));
        $line = 1;
        $column = 1;

        for ($index = 0; $index < $offset; $index++) {
            if ($this->input[$index] === "\n") {
                $line++;
                $column = 1;
                continue;
            }

            $column++;
        }

        return ['line' => $line, 'column' => $column];
    }

    /**
     * Returns a compact excerpt around an offset for diagnostics.
     */
    public function snippet(int $offset, int $radius = 20): string
    {
        $start = max(0, $offset - $radius);
        $length = min($this->length(), $offset + $radius) - $start;
        $snippet = substr($this->input, $start, $length);
        $snippet = str_replace(["\r", "\n", "\t"], ['\\r', '\\n', '\\t'], $snippet);

        return sprintf('"%s"', $snippet);
    }
}
