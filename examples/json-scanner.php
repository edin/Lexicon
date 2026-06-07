<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Fixed;
use Lexicon\Lexer\Attributes\Literal;
use Lexicon\Lexer\Attributes\Symbol;
use Lexicon\Lexer\Attributes\Trivia;
use Lexicon\Lexer\Attributes\Unknown;
use Lexicon\Lexer\Debug\TokenTable;
use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Matchers\JsonNumberTokenMatcher;
use Lexicon\Lexer\Matchers\JsonStringTokenMatcher;
use Lexicon\Lexer\Matchers\WhitespaceTokenMatcher;

enum JsonExampleToken
{
    #[Symbol('{')]
    case LeftBrace;

    #[Symbol('}')]
    case RightBrace;

    #[Symbol('[')]
    case LeftBracket;

    #[Symbol(']')]
    case RightBracket;

    #[Symbol(':')]
    case Colon;

    #[Symbol(',')]
    case Comma;

    #[Fixed('true')]
    case True;

    #[Fixed('false')]
    case False;

    #[Fixed('null')]
    case Null;

    #[Literal(JsonStringTokenMatcher::class)]
    case StringLiteral;

    #[Literal(JsonNumberTokenMatcher::class)]
    case Number;

    #[Trivia(WhitespaceTokenMatcher::class)]
    case Whitespace;

    #[Unknown]
    case Unknown;

    #[EndOfFile]
    case EndOfFile;
}

$source = '{"name": "Lexicon", "ok": true, "values": [1, -2.5e3, null]}';
$tokens = Lexer::from(JsonExampleToken::class)->scan($source);

echo TokenTable::format($tokens, color: true) . PHP_EOL;
