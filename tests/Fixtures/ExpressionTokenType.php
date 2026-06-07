<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Literal;
use Lexicon\Lexer\Attributes\Symbol;
use Lexicon\Lexer\Attributes\Trivia;
use Lexicon\Lexer\Attributes\Unknown;
use Lexicon\Lexer\Matchers\IntegerTokenMatcher;
use Lexicon\Lexer\Matchers\WhitespaceTokenMatcher;

enum ExpressionTokenType
{
    #[Literal(IntegerTokenMatcher::class)]
    case Integer;

    #[Symbol('+')]
    case Plus;

    #[Symbol('-')]
    case Minus;

    #[Symbol(',')]
    case Comma;

    #[Symbol('(')]
    case OpenParen;

    #[Symbol(')')]
    case CloseParen;

    #[Trivia(WhitespaceTokenMatcher::class)]
    case Whitespace;

    #[Unknown]
    case Unknown;

    #[EndOfFile]
    case EndOfFile;
}
