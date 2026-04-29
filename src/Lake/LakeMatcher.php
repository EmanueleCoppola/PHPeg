<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Lake;

use EmanueleCoppola\PHPeg\Ast\AstNode;
use EmanueleCoppola\PHPeg\Expression\LakeExpression;
use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Matches lake expressions against the compiled stop plan.
 */
class LakeMatcher
{
    /**
     * Attempts to match a lake expression at the given offset.
     */
    public static function match(ParseContext $context, LakeExpression $lake, int $offset): ?MatchResult
    {
        $plan = $context->lakePlan();
        $sequences = $plan->stopSequencesFor($lake);
        if ($sequences === []) {
            $context->recordFailure($offset, $lake->describe());

            return null;
        }

        $cursor = $offset;
        $length = $context->input()->length();

        while ($cursor <= $length) {
            foreach ($sequences as $sequence) {
                if (!$sequence->canStartAt($context, $cursor)) {
                    continue;
                }

                $result = $context->withBannedLakeIds(
                    [spl_object_id($lake) => $cursor],
                    fn () => $sequence->match($context, $cursor),
                );

                if ($result !== null) {
                    return self::buildResult($context, $lake, $offset, $cursor);
                }
            }

            if ($cursor === $length) {
                break;
            }

            $cursor++;
        }

        foreach ($sequences as $sequence) {
            $expected = $sequence->firstExpression()?->describe() ?? 'EOF';
            $context->recordFailure($length, $expected);
        }

        return null;
    }

    /**
     * Builds the AST result for a matched lake.
     */
    private static function buildResult(ParseContext $context, LakeExpression $lake, int $startOffset, int $endOffset): MatchResult
    {
        if (!$lake->capture()) {
            return new MatchResult($startOffset, $endOffset);
        }

        $node = new AstNode(
            $lake->name() ?? 'Lake',
            $context->options()->lazyNodeText() ? null : $context->input()->slice($startOffset, $endOffset),
            $startOffset,
            $endOffset,
            [],
            ['kind' => 'lake'],
            true,
            null,
            $context->options()->lazyNodeText() ? $context->input() : null,
        );

        return new MatchResult($startOffset, $endOffset, [$node]);
    }
}
