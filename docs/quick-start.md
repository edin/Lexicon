# Quick Start

Define tokens in your own enum:

```php
use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Fixed;
use Lexicon\Lexer\Attributes\Identifier;
use Lexicon\Lexer\Attributes\Keyword;
use Lexicon\Lexer\Attributes\Literal;
use Lexicon\Lexer\Attributes\Symbol;
use Lexicon\Lexer\Attributes\Trivia;
use Lexicon\Lexer\Attributes\Unknown;
use Lexicon\Lexer\Matchers\NumberTokenMatcher;
use Lexicon\Lexer\Matchers\WhitespaceTokenMatcher;

enum MiniToken
{
    #[Identifier]
    case Identifier;

    #[Keyword('let')]
    case LetKeyword;

    #[Literal(NumberTokenMatcher::class)]
    case Number;

    #[Fixed('true')]
    case True;

    #[Symbol('=')]
    case Equals;

    #[Trivia(WhitespaceTokenMatcher::class)]
    case Whitespace;

    #[Unknown]
    case Unknown;

    #[EndOfFile]
    case EndOfFile;
}
```

Scan source:

```php
use Lexicon\Lexer\Lexer;

$lexer = Lexer::from(MiniToken::class);
$tokens = $lexer->scan('let answer = 42');

foreach ($tokens as $token) {
    echo $token . PHP_EOL;
}
```

Diagnostics are available after scanning:

```php
if ($lexer->diagnostics->hasErrors()) {
    foreach ($lexer->diagnostics->all() as $diagnostic) {
        echo $diagnostic->message . PHP_EOL;
    }
}
```

Each token has:

```php
$token->type;
$token->group;
$token->value;
$token->location;
$token->mode;
$token->leadingTrivia;
$token->span();
$token->fullText();
```
