<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Literal;
use Lexicon\Lexer\Attributes\Trivia;
use Lexicon\Lexer\Attributes\Unknown;
use Lexicon\Lexer\Matchers\WhitespaceTokenMatcher;
use Lexicon\Lexer\Matchers\WordTokenMatcher;

enum WordTokenType
{
    #[Literal(WordTokenMatcher::class)]
    case Word;

    #[Trivia(WhitespaceTokenMatcher::class)]
    case Whitespace;

    #[Unknown]
    case Unknown;

    #[EndOfFile]
    case EndOfFile;
}
