# Fluent PHP Builder

The fluent PHP builder is the most direct way to define a grammar in PHPPeg.
It keeps the grammar in native PHP code and compiles it into the same immutable `Grammar` model used by the loaders.

## When To Use It

Use the builder when:

- you want the grammar to live alongside application code
- you prefer explicit PHP over a grammar file format
- you want to compose expressions programmatically

## Basic Shape

```php
use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;

$g = GrammarBuilder::create();

$grammar = $g->grammar('Start')
    ->rule('Expression', ...)
    ->rule('Start', ...)
    ->build();
```

The builder has four steps:

1. `GrammarBuilder::create()` creates a new builder instance.
2. `grammar($startRule)` optionally declares the start rule name.
3. `rule($name, $expression)` adds or replaces a rule.
4. `build()` returns the immutable `Grammar`.

If you do not call `grammar()`, the first rule you add becomes the start rule.

## Builder Methods

### Grammar Definition

- `grammar(string $startRule): self`
- `rule(string $name, ExpressionInterface $expression): self`
- `build(): Grammar`

### Expression Constructors

- `literal(string $literal): ExpressionInterface`
- `regex(string $pattern): ExpressionInterface`
- `charClass(string $pattern): ExpressionInterface`
- `seq(ExpressionInterface ...$expressions): ExpressionInterface`
- `choice(ExpressionInterface ...$expressions): ExpressionInterface`
- `zeroOrMore(ExpressionInterface $expression): ExpressionInterface`
- `oneOrMore(ExpressionInterface $expression): ExpressionInterface`
- `optional(ExpressionInterface $expression): ExpressionInterface`
- `ref(string $name): ExpressionInterface`
- `any(): ExpressionInterface`
- `eof(): ExpressionInterface`
- `and(ExpressionInterface $expression): ExpressionInterface`
- `not(ExpressionInterface $expression): ExpressionInterface`

### Aliases

- `one()` is an alias for `oneOrMore()`
- `many()` is an alias for `zeroOrMore()`
- `maybe()` is an alias for `optional()`
- `or()` is an alias for `choice()`

## Expression Semantics

### `literal()`

Matches an exact string at the current offset.

```php
$g->literal('if');
```

### `regex()`

Matches an anchored regular expression at the current offset.
Use this for token-like fragments where a direct regex is clearer than a character class loop.

```php
$g->regex('[0-9]+');
```

### `charClass()`

Matches a single character from a bracket expression.

```php
$g->charClass('[a-zA-Z_]');
```

### `seq()`

Matches each expression in order.

```php
$g->seq($g->ref('Name'), $g->literal('='), $g->ref('Value'));
```

`seq()` accepts one or more `ExpressionInterface` instances.

### `choice()`

Tries alternatives in order and returns the first successful match.

```php
$g->choice($g->ref('String'), $g->ref('Number'), $g->ref('Identifier'));
```

`choice()` accepts one or more `ExpressionInterface` instances.

### `zeroOrMore()`

Matches the wrapped expression zero or more times.

```php
$g->zeroOrMore($g->ref('Item'));
```

Use it with any single `ExpressionInterface`:

- a rule reference, such as `$g->ref('Item')`
- a literal, such as `$g->literal(',')`
- a grouped sequence, such as `$g->seq($g->literal(','), $g->ref('Item'))`
- another quantified expression, when you really want nested repetition

### `oneOrMore()`

Matches the wrapped expression one or more times.

```php
$g->oneOrMore($g->charClass('[0-9]'));
```

Use it with any single `ExpressionInterface`.

### `optional()`

Matches the wrapped expression zero or one time.

```php
$g->optional($g->literal('-'));
```

Use it with any single `ExpressionInterface`.

### `ref()`

Creates a named rule reference.

```php
$g->ref('Expression');
```

### `any()`

Matches any single character.

```php
$g->any();
```

### `eof()`

Matches the end of input.

```php
$g->seq($g->ref('Expression'), $g->eof());
```

### `and()` and `not()`

Create positive and negative lookahead predicates.

```php
$g->and($g->literal('!'));
$g->not($g->literal(')'));
```

Predicates do not consume input. They only test whether the wrapped expression would match.

Use them with any single `ExpressionInterface`, usually a literal, reference, or short sequence.

## Example Grammar

This example is shared across the three grammar styles in this repository.

```php
use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;

$g = GrammarBuilder::create();

$grammar = $g->grammar('Start')
    ->rule('Number', $g->oneOrMore($g->charClass('[0-9]')))
    ->rule('Factor', $g->choice(
        $g->ref('Number'),
        $g->seq($g->literal('('), $g->ref('Expression'), $g->literal(')')),
    ))
    ->rule('Term', $g->seq(
        $g->ref('Factor'),
        $g->zeroOrMore($g->seq(
            $g->choice($g->literal('*'), $g->literal('/')),
            $g->ref('Factor'),
        )),
    ))
    ->rule('Expression', $g->seq(
        $g->ref('Term'),
        $g->zeroOrMore($g->seq(
            $g->choice($g->literal('+'), $g->literal('-')),
            $g->ref('Term'),
        )),
    ))
    ->rule('Start', $g->seq($g->ref('Expression'), $g->eof()))
    ->build();
```

## Runtime Output

The builder compiles to the same runtime model as the loaders.

- `Grammar::rules()` returns the named rule map.
- `Grammar::startRule()` returns the configured start rule name.
- `Grammar::parse()` returns a `ParseResult`.
- `Grammar::parseDocument()` returns a `ParsedDocument`.

## Practical Notes

- Use `literal()` for exact punctuation and keywords.
- Use `charClass()` for character-by-character scanning.
- Use `regex()` when a token is easier to describe as a single anchored pattern.
- Keep recursive references explicit with `ref()`.
- Prefer `parseDocument()` when you need source-preserving editing or selector queries.
- For AST querying, mutation, and source-preserving printing details, see [`docs/ast/README.md`](../ast/README.md).
