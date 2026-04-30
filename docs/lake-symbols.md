# Lake Symbols

This repository's lake nodes are inspired by the paper "Lake Symbols for Island Parsing" by Okuda, K. and Chiba, S.

Source paper:

- [Lake Symbols for Island Parsing](papers/lake-symbols-for-island-parsing.pdf)
- arXiv: https://arxiv.org/abs/2010.16306

## The Idea

Lake symbols support island parsing: you describe the structured regions you care about, and the parser consumes the surrounding irrelevant text until it reaches a safe stopping point derived from grammar context.

That avoids writing large, fragile water rules by hand.

## Why It Matters

Lake symbols are useful when:

- the input is mostly unstructured text
- only a few embedded constructs matter
- the host language is incomplete or unknown
- you want source-preserving editing over partially structured documents

## How PHPeg Uses The Idea

PHPeg implements lake nodes as an opt-in grammar expression integrated with:

- the immutable grammar model
- the parser runtime
- AST generation
- source-preserving printing

The implementation adapts the concept for PHP and keeps it consistent with the rest of the library.

## Practical Summary

In this repository, lake nodes are a small abstraction for saying:

- parse the interesting island here
- let the parser handle the surrounding water
- keep the skipped text intact for query, mutation, and printing
