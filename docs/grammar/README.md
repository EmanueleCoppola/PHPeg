# Grammar Reference

This directory documents the three supported grammar definition styles in PHPPeg.

## Pages

- [Fluent PHP Builder](fluent-php-builder.md)
- [CleanPeg Loader](clean-peg-loader.md)
- [Classic PEG Loader](classic-peg-loader.md)

## Shared Runtime Model

All three styles compile to the same runtime model:

- `Grammar` stores named `Rule` objects plus a start rule.
- `Grammar::parse()` returns a `ParseResult`.
- `Grammar::parseDocument()` returns a `ParsedDocument` for source-preserving editing.
- Parsed rules produce `AstNode` trees with rule names, offsets, text, children, attributes, and mutation support.

The style-specific pages below focus on syntax. For AST querying and mutation, see the shared model sections inside each page and the existing AST docs.
