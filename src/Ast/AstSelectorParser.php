<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Ast;

use EmanueleCoppola\PHPeg\Error\AstQueryError;

/**
 * Parses a small CSS-like selector language for AST nodes.
 */
class AstSelectorParser
{
    public static function parse(string $selector): AstSelector
    {
        $selector = trim($selector);
        if ($selector === '') {
            throw new AstQueryError('Selector cannot be empty.');
        }

        $length = strlen($selector);
        $offset = 0;
        $steps = [];
        $pendingCombinator = 'descendant';

        while ($offset < $length) {
            while ($offset < $length && ctype_space($selector[$offset])) {
                $offset++;
                if ($pendingCombinator !== 'child') {
                    $pendingCombinator = 'descendant';
                }
            }

            if ($offset < $length && $selector[$offset] === '>') {
                $pendingCombinator = 'child';
                $offset++;
                continue;
            }

            if ($offset >= $length) {
                break;
            }

            if (!preg_match('/\G([A-Za-z_][A-Za-z0-9_]*)/A', $selector, $matches, 0, $offset)) {
                throw new AstQueryError(sprintf('Invalid selector near: %s', substr($selector, $offset)));
            }

            $name = $matches[1];
            $offset += strlen($name);
            $attributes = [];
            $pseudo = null;
            $pseudoArgument = null;

            while ($offset < $length) {
                if ($selector[$offset] === '[') {
                    $end = strpos($selector, ']', $offset);
                    if ($end === false) {
                        throw new AstQueryError('Unclosed attribute selector.');
                    }

                    $body = substr($selector, $offset + 1, $end - $offset - 1);
                    if (!preg_match('/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*"([^"]*)"\s*$/', $body, $attributeMatches)) {
                        throw new AstQueryError(sprintf('Invalid attribute selector: [%s]', $body));
                    }

                    $attributes[$attributeMatches[1]] = $attributeMatches[2];
                    $offset = $end + 1;
                    continue;
                }

                if ($selector[$offset] === ':') {
                    if (preg_match('/\G:first/A', $selector, $pseudoMatches, 0, $offset) === 1) {
                        $pseudo = 'first';
                        $offset += strlen($pseudoMatches[0]);
                        continue;
                    }

                    if (preg_match('/\G:last/A', $selector, $pseudoMatches, 0, $offset) === 1) {
                        $pseudo = 'last';
                        $offset += strlen($pseudoMatches[0]);
                        continue;
                    }

                    if (preg_match('/\G:nth-child\((\d+)\)/A', $selector, $pseudoMatches, 0, $offset) === 1) {
                        $pseudo = 'nth-child';
                        $pseudoArgument = (int) $pseudoMatches[1];
                        $offset += strlen($pseudoMatches[0]);
                        continue;
                    }

                    throw new AstQueryError(sprintf('Invalid pseudo selector near: %s', substr($selector, $offset)));
                }

                break;
            }

            $steps[] = new AstSelectorStep($name, $pendingCombinator, $attributes, $pseudo, $pseudoArgument);
            $pendingCombinator = 'descendant';
        }

        return new AstSelector($steps);
    }
}
