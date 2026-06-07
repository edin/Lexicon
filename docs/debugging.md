# Debugging Tokens

## Token Table

```php
use Lexicon\Lexer\Debug\TokenTable;

echo TokenTable::format($tokens);
```

Options:

```php
TokenTable::format(
    tokens: $tokens,
    includeTrivia: true,
    maxValueLength: 60,
    color: false,
);
```

The table includes:

- kind
- group
- mode
- value
- line
- column
- span

## Token Debug Info

`Token`, `Location`, and `SourceFile` implement compact debug output so `print_r($token)` does not dump the full source text.

## Lossless Reconstruction

Trivia is attached as leading trivia:

```php
$source = implode('', array_map(
    fn (Token $token): string => $token->fullText(),
    $tokens
));
```
