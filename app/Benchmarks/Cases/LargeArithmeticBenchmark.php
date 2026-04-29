<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks\Cases;

use EmanueleCoppola\PHPeg\Result\ParseResult;

/**
 * Measures arithmetic parsing on a long precedence-heavy expression.
 */
class LargeArithmeticBenchmark extends AbstractBenchmarkCase
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'Large arithmetic expression';
    }

    /**
     * @inheritDoc
     */
    public function slug(): string
    {
        return 'arithmetic';
    }

    /**
     * @inheritDoc
     */
    public function input(string $scale): string
    {
        $operations = $this->sizeForScale($scale, [
            'small' => 800,
            'medium' => 1800,
            'large' => 4000,
        ]);

        $expression = '(1.25 + 2)';
        $operators = [' + ', ' - ', ' * ', ' / '];

        for ($index = 0; $index < $operations; $index++) {
            $operator = $operators[$index % count($operators)];
            $number = sprintf('%d.%02d', ($index % 97) + 3, ($index * 7) % 100);

            if ($index % 3 === 0) {
                $expression = '(' . $expression . $operator . '(' . $number . ' + ' . (($index % 11) + 1) . '))';
                continue;
            }

            $expression = '(' . $expression . $operator . $number . ')';
        }

        return $expression;
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
Number = r'\d+(?:\.\d+)?'
Factor = Number / "(" Expression ")"
Term = Factor ((" * " / " / ") Factor)*
Expression = Term ((" + " / " - ") Term)*
Start = Expression EOF
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
