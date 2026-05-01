<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Parser\Packrat;

use EmanueleCoppola\PHPeg\Error\LeftRecursionException;
use EmanueleCoppola\PHPeg\Grammar\Grammar;
use EmanueleCoppola\PHPeg\Parser\InputBuffer;
use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Parser\ParserOptions;
use EmanueleCoppola\PHPeg\Parser\RuleMemoEntry;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Parse context for the standard packrat runtime.
 */
class PackratParseContext extends ParseContext
{
    /**
     * Initializes a new PackratParseContext instance.
     */
    public function __construct(
        Grammar $grammar,
        InputBuffer $input,
        ParserOptions $options = new ParserOptions(),
    ) {
        parent::__construct($grammar, $input, $options);
    }

    /**
     * Matches a named rule with memoization.
     */
    public function matchRule(string $ruleName, int $offset): ?MatchResult
    {
        $rule = $this->grammar->rule($ruleName);
        if ($rule === null) {
            $this->recordFailure($offset, sprintf('rule <%s>', $ruleName));

            return null;
        }

        $entry = $this->memo[$ruleName][$offset] ?? null;
        if ($entry instanceof RuleMemoEntry) {
            if ($entry->isEvaluating()) {
                throw new LeftRecursionException($ruleName, $offset);
            }

            return $entry->result();
        }

        $entry = new RuleMemoEntry();
        $this->memo[$ruleName][$offset] = $entry;

        try {
            $entry->beginEvaluation();
            $result = $rule->match($this, $offset);
        } finally {
            $entry->finishEvaluation();
        }

        $entry->setResult($result);

        if ($this->memoizationEnabled) {
            $this->rememberRuleResult($ruleName, $offset, $entry);
        } else {
            unset($this->memo[$ruleName][$offset]);
            if ($this->memo[$ruleName] === []) {
                unset($this->memo[$ruleName]);
            }
        }

        return $result;
    }
}
