<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Identifier;
use Lexicon\Lexer\Attributes\RegexPattern;
use Lexicon\Lexer\Attributes\Symbol;
use Lexicon\Lexer\Attributes\Trivia;
use Lexicon\Lexer\Attributes\Unknown;
use Lexicon\Lexer\Matchers\WhitespaceTokenMatcher;

enum StackModeTokenType
{
    #[Identifier(in: StackMode::Code)]
    case Identifier;

    #[Symbol('"', in: StackMode::Code, push: StackMode::String)]
    case StringStart;

    #[RegexPattern('/\A[^"]+/', in: StackMode::String)]
    case StringText;

    #[Symbol('"', in: StackMode::String, pop: true)]
    case StringEnd;

    #[Trivia(WhitespaceTokenMatcher::class, in: StackMode::Code)]
    case Whitespace;

    #[Unknown]
    case Unknown;

    #[EndOfFile]
    case EndOfFile;
}
