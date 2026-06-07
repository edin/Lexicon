# Token Attributes

Tokens are enum cases annotated with attributes.

## Identifier

```php
#[Identifier]
case Identifier;
```

Identifiers are scanned last and can be coerced into `#[Keyword]` tokens.

## Keyword

```php
#[Keyword('if')]
case IfKeyword;
```

Keywords are intended for identifier-like languages. If an enum defines keywords, it must also define one identifier token.

## Symbol

```php
#[Symbol('<=')]
case LessThanOrEqual;
```

Symbols are fixed text tokens. Fixed text tokens are matched longest first.

## Fixed

```php
#[Fixed('true')]
case True;
```

Use `Fixed` for direct fixed text that is not a symbol or identifier-coerced keyword, such as JSON `true`, `false`, and `null`.

## Literal

```php
#[Literal(NumberTokenMatcher::class)]
case Number;
```

Use `Literal` for matcher-backed literal tokens.

## RegexPattern

```php
#[RegexPattern('/\A@[A-Za-z_][A-Za-z0-9_]*/')]
case AttributeName;
```

Regex patterns must match from the current scanner position.

## Trivia

```php
#[Trivia(WhitespaceTokenMatcher::class)]
case Whitespace;
```

Trivia tokens are attached to the next non-trivia token as leading trivia.

## Unknown

```php
#[Unknown]
case Unknown;
```

Unknown tokens preserve unmatched source text. Unknown characters are batched until a known token can match again.

## EndOfFile

```php
#[EndOfFile]
case EndOfFile;
```

Each token enum must define exactly one EOF token.

## Validation

Lexicon validates token enums and reports configuration errors for:

- missing EOF token
- duplicate EOF token
- duplicate unknown token
- duplicate identifier token
- duplicate fixed text in the same mode
- keywords without an identifier token
- empty fixed text
