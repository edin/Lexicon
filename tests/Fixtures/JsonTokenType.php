<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Fixed;
use Lexicon\Lexer\Attributes\Literal;
use Lexicon\Lexer\Attributes\Symbol;
use Lexicon\Lexer\Attributes\Trivia;
use Lexicon\Lexer\Attributes\Unknown;
use Lexicon\Lexer\Matchers\JsonNumberTokenMatcher;
use Lexicon\Lexer\Matchers\JsonStringTokenMatcher;
use Lexicon\Lexer\Matchers\WhitespaceTokenMatcher;

enum JsonTokenType
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
