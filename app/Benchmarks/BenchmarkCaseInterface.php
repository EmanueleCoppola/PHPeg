<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

use EmanueleCoppola\PHPeg\Grammar\Grammar;
use EmanueleCoppola\PHPeg\Result\ParseResult;

/**
 * Describes a single parser benchmark case.
 */
interface BenchmarkCaseInterface
{
    /**
     * Returns the human-readable benchmark name.
     */
    public function name(): string;

    /**
     * Returns the stable benchmark identifier used in filters and history.
     */
    public function slug(): string;

    /**
     * Builds the grammar for the provided benchmark scale.
     */
    public function grammar(string $scale): Grammar;

    /**
     * Generates deterministic input for the provided benchmark scale.
     */
    public function input(string $scale): string;

    /**
     * Verifies the parse result for the generated input.
     */
    public function validate(ParseResult $result, string $input): void;
}
