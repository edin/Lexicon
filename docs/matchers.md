# Matchers

Matchers implement `Lexicon\Lexer\Matchers\ITokenMatcher`.

## Generic Matchers

- `IdentifierTokenMatcher`
- `IntegerTokenMatcher`
- `DecimalTokenMatcher`
- `NumberTokenMatcher`
- `StringTokenMatcher`
- `CharacterTokenMatcher`
- `WhitespaceTokenMatcher`
- `LineCommentTokenMatcher`
- `BlockCommentTokenMatcher`
- `RegexTokenMatcher`
- `WordTokenMatcher`

## JSON Matchers

- `JsonStringTokenMatcher`
- `JsonNumberTokenMatcher`

Example:

```php
#[Literal(JsonStringTokenMatcher::class)]
case StringLiteral;

#[Literal(JsonNumberTokenMatcher::class)]
case Number;
```

## XML Matchers

- `XmlNameTokenMatcher`
- `XmlTextTokenMatcher`
- `XmlCommentTokenMatcher`
- `XmlCdataTokenMatcher`
- `XmlProcessingInstructionTokenMatcher`

Example:

```php
#[Literal(XmlTextTokenMatcher::class, in: XmlMode::Text)]
case Text;

#[Literal(XmlNameTokenMatcher::class, in: XmlMode::Tag, group: TokenGroup::Identifier)]
case Name;
```

## Custom Matchers

```php
use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Matchers\ITokenMatcher;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenMetadata;

final class PercentTokenMatcher implements ITokenMatcher
{
    public function __construct(private readonly TokenMetadata $metadata)
    {
    }

    public function match(Lexer $lexer): ?Token
    {
        // Return null when not matched.
        // Advance the lexer when matched.
    }
}
```
