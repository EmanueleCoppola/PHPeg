<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Ast;

use EmanueleCoppola\PHPeg\Ast\AstSelector;
use EmanueleCoppola\PHPeg\Ast\AstSelectorStep;
use PHPUnit\Framework\TestCase;

class AstSelectorTest extends TestCase
{
    /**
     * Verifies selector step storage.
     */
    public function testExposesSelectorSteps(): void
    {
        $selector = new AstSelector([
            new AstSelectorStep('Block', 'descendant', [], null, null),
            new AstSelectorStep('Directive', 'child', ['name' => 'listen'], 'first', null),
        ]);

        self::assertCount(2, $selector->steps());
        self::assertSame('Block', $selector->steps()[0]->name());
        self::assertSame('Directive', $selector->steps()[1]->name());
    }
}