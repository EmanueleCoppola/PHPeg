<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Lake;

use EmanueleCoppola\PHPeg\Grammar\Grammar;

/**
 * Caches compiled lake plans per immutable grammar instance.
 */
class LakePlanCache
{
    /**
     * @var array<int, LakePlan>
     */
    private static array $cache = [];

    /**
     * Returns the cached lake plan for the provided grammar, compiling it on demand.
     */
    public static function forGrammar(Grammar $grammar): LakePlan
    {
        $grammarId = spl_object_id($grammar);

        if (isset(self::$cache[$grammarId])) {
            return self::$cache[$grammarId];
        }

        $plan = LakeAnalyzer::analyze($grammar);
        self::$cache[$grammarId] = $plan;

        return $plan;
    }
}
