<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Identifier;
use Lexicon\Lexer\Attributes\Keyword;
use Lexicon\Lexer\Attributes\Literal;
use Lexicon\Lexer\Attributes\Symbol;
use Lexicon\Lexer\Attributes\Trivia;
use Lexicon\Lexer\Attributes\Unknown;
use Lexicon\Lexer\Matchers\BlockCommentTokenMatcher;
use Lexicon\Lexer\Matchers\CharacterTokenMatcher;
use Lexicon\Lexer\Matchers\LineCommentTokenMatcher;
use Lexicon\Lexer\Matchers\NumberTokenMatcher;
use Lexicon\Lexer\Matchers\StringTokenMatcher;
use Lexicon\Lexer\Matchers\WhitespaceTokenMatcher;

enum TestTokenType
{
    #[Identifier]
    case Identifier;

    #[Literal(NumberTokenMatcher::class)]
    case Number;

    #[Literal(StringTokenMatcher::class)]
    case StringLiteral;

    #[Literal(CharacterTokenMatcher::class)]
    case CharacterLiteral;

    #[Keyword('if')]
    case IfKeyword;

    #[Keyword('foreach')]
    case ForeachKeyword;

    #[Keyword('true')]
    case TrueKeyword;

    #[Keyword('false')]
    case FalseKeyword;

    #[Keyword('null')]
    case NullKeyword;

    #[Symbol('->')]
    case Arrow;

    #[Symbol('=>')]
    case FatArrow;

    #[Symbol('...')]
    case Ellipsis;

    #[Symbol('..')]
    case DotDot;

    #[Symbol('.')]
    case Dot;

    #[Symbol('<=>')]
    case Spaceship;

    #[Symbol('<=')]
    case LessThanOrEqual;

    #[Symbol('<')]
    case LessThan;

    #[Symbol('/')]
    case Slash;

    #[Symbol('-')]
    case Minus;

    #[Trivia(LineCommentTokenMatcher::class)]
    case Comment;

    #[Trivia(BlockCommentTokenMatcher::class)]
    case MultilineComment;

    #[Trivia(WhitespaceTokenMatcher::class)]
    case Whitespace;

    #[Unknown]
    case Unknown;

    #[EndOfFile]
    case EndOfFile;
}
