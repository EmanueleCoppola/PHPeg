# CLI

PHPPeg includes a command-line parser that loads a grammar file, parses an input file, optionally filters the AST with a selector, and exports JSON.

## Command

Recommended form:

```bash
php bin/phpeg parse --grammar=path/to/grammar.cleanpeg -i path/to/input.txt --query='Block[name="server"]' --json-style=simple -o output.json
```

## Required Input

The command needs two required paths:

- `--grammar=PATH`
  - the grammar file to load
  - it can be a `.peg` or `.cleanpeg` file
  - may be relative or absolute
  - relative paths are resolved from the current working directory
- `-i, --input=PATH`
  - the input file to parse
  - may be relative or absolute
  - the short `-i` form is convenient in shell pipelines

If either flag is missing, the command returns a failure response.

## Grammar Format

`--grammar-format` accepts:

- `auto`
- `cleanpeg`
- `peg`

`auto` is the default. It infers the format from the grammar file extension:

- `.cleanpeg` resolves to `cleanpeg`
- `.peg` resolves to `peg`

If you set `--grammar-format=auto`, the command detects the format automatically from `--grammar`.

If you use `cleanpeg` or `peg` explicitly, the grammar format is forced and no inference is applied.

## Query

`--query=SELECTOR` filters the exported nodes using the same selector syntax as `ParsedDocument::query()`.

Examples:

- `Block[name="server"]`
- `Lake[kind="lake"]`
- `Block > Directive:first`

If `--query` is omitted, the command exports the parsed root node.

## Output

The command writes JSON to stdout by default.

If you pass `-o` or `--output`, the JSON is written to that path instead.

The output path can use any extension you want, for example:

```bash
php bin/phpeg parse --grammar=grammar.cleanpeg -i input.txt -o result.json
```

The file still contains JSON. The extension is only a filename choice, not a format restriction.

## Output Styles

### `full`

`full` is the detailed schema. It includes:

- grammar metadata
- input metadata
- parse offsets
- query metadata
- detailed node data

Each node contains:

- `name`
- `text`
- `originalText`
- offsets
- state flags
- `lake`
- raw `attributes`
- derived `semantic` fields
- recursive `children`

<details>
<summary>Example JSON</summary>

```json
{
  "success": true,
  "query": {
    "selector": "Block[name=\"server\"]",
    "count": 1
  },
  "matches": [
    {
      "name": "Block",
      "text": "server { ... }",
      "originalText": "server { ... }",
      "startOffset": 128,
      "endOffset": 512,
      "isOriginal": true,
      "isModified": false,
      "isInserted": false,
      "isRemoved": false,
      "lake": false,
      "attributes": {},
      "semantic": {
        "text": "server { ... }",
        "type": "Block",
        "name": "server",
        "value": null
      },
      "children": []
    }
  ]
}
```

</details>

### `simple`

`simple` is the compact schema. It keeps only:

- `name`
- `text`
- `lake`
- recursive `children`

This is the best choice when you want to pipe the output into `jq` and inspect only the fields you need.

<details>
<summary>Example JSON</summary>

```json
{
  "success": true,
  "matches": [
    {
      "name": "Lake",
      "text": "middle",
      "lake": true,
      "children": []
    }
  ]
}
```

</details>

## Example Workflows

### Print to stdout

```bash
php bin/phpeg parse \
  --grammar=examples/nginx-config-edit/nginx-config-grammar.cleanpeg \
  -i examples/nginx-config-edit/nginx-config.conf \
  --query='Block[name="server"]' \
  --json-style=full
```

### Write to a file

```bash
php bin/phpeg parse \
  --grammar=examples/nginx-config-edit/nginx-config-grammar.cleanpeg \
  -i examples/nginx-config-edit/nginx-config.conf \
  --query='Block[name="server"]' \
  --json-style=simple \
  -o server-node.json
```

### Pipe into `jq`

```bash
php bin/phpeg parse \
  --grammar=examples/nginx-config-edit/nginx-config-grammar.cleanpeg \
  -i examples/nginx-config-edit/nginx-config.conf \
  --query='Block[name="server"]' \
  --json-style=simple \
  | jq '.matches[0] | {name, text, lake}'
```

## Notes

- `--start-rule` is available for grammars that support it.
- The `simple` schema still keeps the tree recursive, so nested nodes remain queryable with `jq`.
- Parse errors are reported as JSON with the error message and location.

## Related Docs

- [AST query](query.md)
- [AST reference](ast/README.md)
- [Source-preserving printing](source-preserving-printing.md)
