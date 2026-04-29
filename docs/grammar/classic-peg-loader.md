# Classic PEG Loader

The classic PEG loader reads traditional PEG syntax and compiles it into the same PHPPeg runtime model used by the builder and CleanPeg loader.

Use it when you already have PEG grammar files or you want a traditional grammar notation in a repository.

## When To Use It

Use the classic PEG loader when:

- you have existing `.peg` grammars
- you want a grammar file format that looks like classic PEG
- you want the same parser runtime, AST nodes, and source-preserving edits as the other styles

## Loader API

```php
use EmanueleCoppola\PHPeg\Loader\Peg\PegGrammarLoader;

$loader = new PegGrammarLoader();
$grammar = $loader->fromString($source);
```

### Loading Methods

- `fromString(string $source): Grammar`
- `fromFile(string $path): Grammar`

The loader has no extra configuration options.

## Core Syntax

### Rule Declaration

Each rule uses `<-`:

```peg
name <- expression
```

Rules are separated by whitespace. Newlines are optional.

### Literals

Both single and double quoted strings are supported.

```peg
'if'
"("
")"
```

Escape handling follows the tokenizer used by the loader.

### Character Classes

Character classes are supported directly.

```peg
[0-9]
[a-zA-Z_]
```

### Sequences

Adjacent expressions form a sequence.

```peg
identifier '=' value
```

### Ordered Choice

Ordered choice uses `/`.

```peg
string / number / identifier
```

### Grouping

Parentheses control precedence.

```peg
(number / '(' expression ')')*
```

### Quantifiers

Supported postfix quantifiers:

- `?`
- `*`
- `+`

Examples:

```peg
sign <- ('+' / '-')?
digits <- [0-9]+
list <- item*
```

### Predicates

The loader supports both lookahead operators:

- `&` positive lookahead
- `!` negative lookahead

Examples:

```peg
keyword <- !identifier 'if'
name <- &letter letter+
```

Predicates do not consume input.

### Any Character

`.` matches any single character.

```peg
any <- .
```

### Comments

Line comments starting with `#` or `//` are ignored.

```peg
# this is ignored
// this is also ignored
```

## End Of Input

Classic PEG does not have a built-in `EOF` keyword.

If you need end-of-input matching, define it explicitly:

```peg
EOF <- !.
start <- expression EOF
```

That keeps the syntax portable and makes the end-of-input rule visible in the grammar file.

## Example Grammar

```peg
Number <- [0-9]+
Factor <- Number / '(' Expression ')'
Term <- Factor (('*' / '/') Factor)*
Expression <- Term (('+' / '-') Term)*
EOF <- !.
Start <- Expression EOF
```

## Runtime Output

The classic PEG loader compiles into the same runtime model as the builder and CleanPeg.

- `Grammar::rules()` returns the named rule map.
- `Grammar::startRule()` returns the configured start rule name.
- `Grammar::parse()` returns a `ParseResult`.
- `Grammar::parseDocument()` returns a `ParsedDocument`.

## Practical Notes

- Use classic PEG when you already maintain grammar files in that notation.
- Define `EOF <- !.` explicitly if you need a whole-input match.
- Use `.` and predicates carefully when porting grammars from other PEG tools.
- Prefer `parseDocument()` when the grammar is used for source-preserving editing rather than simple recognition.
- For AST querying, mutation, and source-preserving printing details, see [`docs/ast/README.md`](../ast/README.md).
