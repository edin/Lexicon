<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Literal;
use Lexicon\Lexer\Attributes\Trivia;
use Lexicon\Lexer\Attributes\Unknown;
use Lexicon\Lexer\Matchers\DecimalTokenMatcher;
use Lexicon\Lexer\Matchers\WhitespaceTokenMatcher;

enum DecimalTokenType
{
    #[Literal(DecimalTokenMatcher::class)]
    case Decimal;

    #[Trivia(WhitespaceTokenMatcher::class)]
    case Whitespace;

    #[Unknown]
    case Unknown;

    #[EndOfFile]
    case EndOfFile;
}
