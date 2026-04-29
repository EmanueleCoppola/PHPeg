<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Parser;

/**
 * Provides read-only input access and position calculations.
 */
class InputBuffer
{
    private readonly int $length;

    /**
     * @var list<int>|null
     */
    private ?array $lineBreakOffsets = null;

    public function __construct(
        private readonly string $input,
    ) {
        $this->length = strlen($input);
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
        return $this->length;
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
        if ($offset < 0 || $offset >= $this->length) {
            return null;
        }

        return $this->input[$offset];
    }

    /**
     * @return array{line:int,column:int}
     */
    public function lineAndColumn(int $offset): array
    {
        $offset = max(0, min($offset, $this->length));
        $lineBreakOffsets = $this->lineBreakOffsets();
        $lineIndex = 0;
        $lineCount = count($lineBreakOffsets);

        while ($lineIndex < $lineCount && $lineBreakOffsets[$lineIndex] < $offset) {
            $lineIndex++;
        }

        $lineStart = $lineIndex === 0 ? 0 : $lineBreakOffsets[$lineIndex - 1] + 1;

        return ['line' => $lineIndex + 1, 'column' => ($offset - $lineStart) + 1];
    }

    /**
     * Returns a compact excerpt around an offset for diagnostics.
     */
    public function snippet(int $offset, int $radius = 20): string
    {
        $start = max(0, $offset - $radius);
        $length = min($this->length, $offset + $radius) - $start;
        $snippet = substr($this->input, $start, $length);
        $snippet = str_replace(["\r", "\n", "\t"], ['\\r', '\\n', '\\t'], $snippet);

        return sprintf('"%s"', $snippet);
    }

    /**
     * Returns newline offsets for line and column calculations.
     *
     * @return list<int>
     */
    private function lineBreakOffsets(): array
    {
        if ($this->lineBreakOffsets !== null) {
            return $this->lineBreakOffsets;
        }

        $offsets = [];
        for ($index = 0; $index < $this->length; $index++) {
            if ($this->input[$index] === "\n") {
                $offsets[] = $index;
            }
        }

        $this->lineBreakOffsets = $offsets;

        return $this->lineBreakOffsets;
    }
}
