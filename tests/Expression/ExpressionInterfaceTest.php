<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Expression;

use EmanueleCoppola\PHPeg\Expression\ExpressionInterface;
use EmanueleCoppola\PHPeg\Expression\LiteralExpression;
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