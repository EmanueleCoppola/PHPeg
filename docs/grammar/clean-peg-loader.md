# CleanPeg Loader

CleanPeg is a compact PEG-like syntax that compiles into the same runtime model as the builder and classic PEG loader.

It is designed for concise grammar files that still read clearly in a PHP project.

## When To Use It

Use CleanPeg when:

- you want grammar definitions in a compact text format
- you want a loader that is still easy to read in reviews
- you want a lightweight syntax without giving up the PHPPeg AST runtime

## Loader API

```php
use EmanueleCoppola\PHPeg\Loader\CleanPeg\CleanPegGrammarLoader;

$loader = new CleanPegGrammarLoader();
$grammar = $loader->fromString($source, startRule: 'Start');
```

### Constructor

`new CleanPegGrammarLoader(?string $skipPattern = '[ \t\r\n]*')`

- `skipPattern` controls the optional whitespace skipper inserted before skippable atoms.
- Pass `null` to disable automatic skipping.

### Loading Methods

- `fromString(string $source, ?string $startRule = null): Grammar`
- `fromFile(string $path, ?string $startRule = null): Grammar`

## Core Syntax

### Rule Declaration

Each rule uses `=`:

```cleanpeg
name = expression
```

Rules are separated by newlines.

### Literals

Double-quoted strings are exact matches.

```cleanpeg
"if"
"("
")"
```

Common escapes such as `\"`, `\\`, `\n`, and `\t` are supported.

### Regex Literals

Regex terminals use raw notation:

```cleanpeg
r'[0-9]+'
r'\d*\.\d*|\d+'
```

The pattern is compiled into a builder `regex()` expression and matched at the current offset.

### Sequences

Whitespace between expressions means sequence.

```cleanpeg
expression term
```

### Ordered Choice

Ordered choice uses `/`.

```cleanpeg
string / number / identifier
```

### Grouping

Parentheses control precedence.

```cleanpeg
(number / "(" expression ")")*
```

### Quantifiers

Supported postfix quantifiers:

- `?`
- `*`
- `+`

Examples:

```cleanpeg
sign = ("+" / "-")?
digits = r'[0-9]+'
list = item*
```

### Built-In `EOF`

`EOF` is built in and compiles to the end-of-input expression.

```cleanpeg
start = expression EOF
```

### Comments

Line comments start with `#`.

```cleanpeg
# this is ignored
number = r'[0-9]+'
```

## Whitespace Skipping

CleanPeg can insert a skip expression before literals, regex terminals, rule references, and `EOF`.

Default skip pattern:

```txt
[ \t\r\n]*
```

With the default setting, these grammars:

```cleanpeg
expression = term (("+" / "-") term)*
```

can parse both:

- `1+2`
- `1 + 2`

Disable skipping with:

```php
$loader = new CleanPegGrammarLoader(skipPattern: null);
```

## Example Grammar

```cleanpeg
Number = r'[0-9]+'
Factor = Number / "(" Expression ")"
Term = Factor (("*" / "/") Factor)*
Expression = Term (("+" / "-") Term)*
Start = Expression EOF
```

## Runtime Output

CleanPeg compiles to the same runtime model as the builder.

- `Grammar::rules()` returns the named rule map.
- `Grammar::startRule()` returns the configured start rule name.
- `Grammar::parse()` returns a `ParseResult`.
- `Grammar::parseDocument()` returns a `ParsedDocument`.

## Practical Notes

- Use CleanPeg for compact grammars that still need the full PHPPeg AST runtime.
- Use `EOF` explicitly when you want full-input matching.
- Disable whitespace skipping when token boundaries matter.
- Keep inserted nodes explicit when you plan to print a modified document.
- For AST querying, mutation, and source-preserving printing details, see [`docs/ast/README.md`](../ast/README.md).
