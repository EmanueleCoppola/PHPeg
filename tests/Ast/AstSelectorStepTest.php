<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Ast;

use EmanueleCoppola\PHPeg\Ast\AstSelectorStep;
use PHPUnit\Framework\TestCase;

class AstSelectorStepTest extends TestCase
{
    /**
     * Verifies selector step accessors.
     */
    public function testExposesSelectorStepState(): void
    {
        $step = new AstSelectorStep('Directive', 'child', ['name' => 'listen'], 'first', null);

        self::assertSame('Directive', $step->name());
        self::assertSame('child', $step->combinator());
        self::assertSame(['name' => 'listen'], $step->attributes());
        self::assertSame('first', $step->pseudo());
        self::assertNull($step->pseudoArgument());
    }
}