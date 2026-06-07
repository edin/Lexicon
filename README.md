# Lexicon

Attribute-driven PHP scanner/lexer library.

Lexicon lets you define tokens with PHP enums and attributes:

```php
$tokens = Lexer::from(MyToken::class)->scan($source);
```

It supports fixed tokens, matcher-backed tokens, trivia preservation, unknown token recovery, token spans, diagnostics, debug tables, and user-defined lexer modes.

## Install

```bash
composer install
```

## Test

```bash
composer test
```

## Documentation

- [Quick Start](docs/quick-start.md)
- [Token Attributes](docs/token-attributes.md)
- [Matchers](docs/matchers.md)
- [Modes](docs/modes.md)
- [Parser Primitives](docs/parser.md)
- [Debugging Tokens](docs/debugging.md)
- [Examples](docs/examples.md)

## Run Examples

```bash
php examples/basic-scanner.php
php examples/json-scanner.php
php examples/xml-scanner.php
php examples/math.php
```
