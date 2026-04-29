<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks\Cases;

use EmanueleCoppola\PHPeg\Result\ParseResult;

/**
 * Measures parser behavior on deeply nested recursive calls.
 */
class DeepNestedRecursionBenchmark extends AbstractBenchmarkCase
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'Deep nested recursion';
    }

    /**
     * @inheritDoc
     */
    public function slug(): string
    {
        return 'recursion';
    }

    /**
     * @inheritDoc
     */
    public function input(string $scale): string
    {
        $depth = $this->sizeForScale($scale, [
            'small' => 1000,
            'medium' => 5000,
            'large' => 10000,
        ]);

        return str_repeat('f(', $depth) . 'value' . str_repeat(')', $depth);
    }

    /**
     * @inheritDoc
     */
    public function validate(ParseResult $result, string $input): void
    {
        $this->assertSuccessfulFullMatch($result, $input);
    }

    /**
     * @inheritDoc
     */
    protected function grammarSource(string $scale): string
    {
        return <<<'CLEANPEG'
Call = "f(" Call ")" / "value"
Start = Call EOF
CLEANPEG;
    }

    /**
     * @inheritDoc
     */
    protected function startRule(): string
    {
        return 'Start';
    }
}
