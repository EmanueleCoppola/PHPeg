<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Error;

use EmanueleCoppola\PHPeg\Error\AstMutationError;
use PHPUnit\Framework\TestCase;

class AstMutationErrorTest extends TestCase
{
    /**
     * Verifies the mutation error carries the supplied message.
     */
    public function testExposesTheMessage(): void
    {
        $error = new AstMutationError('cannot mutate');

        self::assertSame('cannot mutate', $error->getMessage());
    }
}