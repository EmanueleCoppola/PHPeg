<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Mutation;

use EmanueleCoppola\PHPPeg\Mutation\InsertPosition;
use PHPUnit\Framework\TestCase;

class InsertPositionTest extends TestCase
{
    /**
     * Verifies all insertion positions are available.
     */
    public function testEnumeratesInsertionPositions(): void
    {
        self::assertSame(['Before', 'After', 'Prepend', 'Append'], array_map(static fn (InsertPosition $position): string => $position->name, InsertPosition::cases()));
    }
}