<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Identifier;
use Lexicon\Lexer\Attributes\Keyword;
use Lexicon\Lexer\Attributes\Literal;
use Lexicon\Lexer\Attributes\Symbol;
use Lexicon\Lexer\Attributes\Trivia;
use Lexicon\Lexer\Attributes\Unknown;
use Lexicon\Lexer\Debug\TokenTable;
use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Matchers\LineCommentTokenMatcher;
use Lexicon\Lexer\Matchers\NumberTokenMatcher;
use Lexicon\Lexer\Matchers\WhitespaceTokenMatcher;

enum ExampleToken
{
    #[Identifier]
    case Identifier;

    #[Keyword('let')]
    case LetKeyword;

    #[Literal(NumberTokenMatcher::class)]
    case Number;

    #[Symbol('=')]
    case Equals;

    #[Symbol(';')]
    case Semicolon;

    #[Trivia(LineCommentTokenMatcher::class)]
    case Comment;

    #[Trivia(WhitespaceTokenMatcher::class)]
    case Whitespace;

    #[Unknown]
    case Unknown;

    #[EndOfFile]
    case EndOfFile;
}

$source = <<<'CODE'
let answer = 42;
// unknown text is preserved
let noisy = @@;
CODE;

$lexer = Lexer::from(ExampleToken::class);
$tokens = $lexer->scan($source);

echo TokenTable::format($tokens, color: true) . PHP_EOL;

if ($lexer->diagnostics->hasErrors()) {
    echo PHP_EOL . 'Diagnostics:' . PHP_EOL;

    foreach ($lexer->diagnostics->all() as $diagnostic) {
        echo sprintf(
            '%s:%d:%d %s',
            $diagnostic->location->file->path,
            $diagnostic->location->line,
            $diagnostic->location->column,
            $diagnostic->message
        ) . PHP_EOL;
    }
}
