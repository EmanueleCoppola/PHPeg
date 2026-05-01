<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Lake;

use EmanueleCoppola\PHPeg\Grammar\Grammar;
use WeakMap;

/**
 * Caches compiled lake plans per immutable grammar instance.
 */
class LakePlanCache
{
    /**
     * @var WeakMap<Grammar, LakePlan>|null
     */
    private static ?WeakMap $cache = null;

    /**
     * Returns the cached lake plan for the provided grammar, compiling it on demand.
     */
    public static function forGrammar(Grammar $grammar): LakePlan
    {
        $cache = self::$cache;
        if ($cache === null) {
            $cache = new WeakMap();
            self::$cache = $cache;
        }

        if ($cache->offsetExists($grammar)) {
            /** @var LakePlan $plan */
            $plan = $cache->offsetGet($grammar);

            return $plan;
        }

        $plan = LakeAnalyzer::analyze($grammar);
        $cache->offsetSet($grammar, $plan);

        return $plan;
    }
}
