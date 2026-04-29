<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Expression;

use EmanueleCoppola\PHPeg\Expression\AbstractExpression;
use EmanueleCoppola\PHPeg\Expression\LiteralExpression;
use PHPUnit\Framework\TestCase;

class AbstractExpressionTest extends TestCase
{
    /**
     * Verifies concrete expressions inherit from the shared base class.
     */
    public function testIsExtendedByConcreteExpressions(): void
    {
        self::assertInstanceOf(AbstractExpression::class, new LiteralExpression('a'));
    }
}