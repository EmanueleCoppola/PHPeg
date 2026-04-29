<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Lake;

use EmanueleCoppola\PHPeg\Expression\LakeExpression;

/**
 * Stores compiled lake stop sequences for a grammar.
 */
class LakePlan
{
    /**
     * @param array<int, list<LakeStopSequence>> $stopSequencesByLakeId
     */
    public function __construct(
        private readonly array $stopSequencesByLakeId,
    ) {
    }

    /**
     * Returns the stop sequences associated with the provided lake node.
     *
     * @return list<LakeStopSequence>
     */
    public function stopSequencesFor(LakeExpression $lake): array
    {
        return $this->stopSequencesByLakeId[spl_object_id($lake)] ?? [];
    }

    /**
     * Returns whether the lake was discovered during analysis.
     */
    public function hasLake(LakeExpression $lake): bool
    {
        return array_key_exists(spl_object_id($lake), $this->stopSequencesByLakeId);
    }

    /**
     * Returns all compiled lakes.
     *
     * @return array<int, list<LakeStopSequence>>
     */
    public function all(): array
    {
        return $this->stopSequencesByLakeId;
    }
}
