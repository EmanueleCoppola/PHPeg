<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Ast;

/**
 * Parsed AST selector.
 */
class AstSelector
{
    /**
     * @param list<AstSelectorStep> $steps
     */
    public function __construct(
        private readonly array $steps,
    ) {
    }

    /**
     * @return list<AstSelectorStep>
     */
    public function steps(): array
    {
        return $this->steps;
    }
}
