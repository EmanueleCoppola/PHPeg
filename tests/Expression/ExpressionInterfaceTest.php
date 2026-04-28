<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Expression;

use EmanueleCoppola\PHPPeg\Expression\ExpressionInterface;
use EmanueleCoppola\PHPPeg\Expression\LiteralExpression;
use PHPUnit\Framework\TestCase;

class ExpressionInterfaceTest extends TestCase
{
    /**
     * Verifies concrete expressions satisfy the public contract.
     */
    public function testIsImplementedByConcreteExpressions(): void
    {
        self::assertInstanceOf(ExpressionInterface::class, new LiteralExpression('a'));
    }
}