# Examples

Run examples from the project root:

```bash
php examples/basic-scanner.php
php examples/json-scanner.php
php examples/xml-scanner.php
php examples/math.php
```

## Basic Scanner

`examples/basic-scanner.php` shows:

- a local enum
- keywords
- identifiers
- numbers
- comments and whitespace trivia
- unknown token diagnostics
- token table output

## JSON Scanner

`examples/json-scanner.php` shows:

- JSON symbols
- fixed JSON literals with `#[Fixed]`
- `JsonStringTokenMatcher`
- `JsonNumberTokenMatcher`

## XML Scanner

`examples/xml-scanner.php` shows:

- user-defined modes
- text/tag state transitions
- XML names
- XML processing instructions
- XML CDATA
- token table mode output

## Math Parser

`examples/math.php` shows:

- math token definitions
- recursive descent parser primitives
- operator precedence for `*`/`/` over `+`/`-`
- grouped expressions
- token table output
- AST printer output
