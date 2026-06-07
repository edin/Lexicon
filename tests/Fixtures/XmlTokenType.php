<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Literal;
use Lexicon\Lexer\Attributes\Symbol;
use Lexicon\Lexer\Attributes\Trivia;
use Lexicon\Lexer\Attributes\Unknown;
use Lexicon\Lexer\Matchers\StringTokenMatcher;
use Lexicon\Lexer\Matchers\WhitespaceTokenMatcher;
use Lexicon\Lexer\Matchers\XmlCdataTokenMatcher;
use Lexicon\Lexer\Matchers\XmlCommentTokenMatcher;
use Lexicon\Lexer\Matchers\XmlNameTokenMatcher;
use Lexicon\Lexer\Matchers\XmlProcessingInstructionTokenMatcher;
use Lexicon\Lexer\Matchers\XmlTextTokenMatcher;
use Lexicon\Lexer\TokenGroup;

enum XmlTokenType
{
    #[Literal(XmlTextTokenMatcher::class, in: XmlMode::Text)]
    case Text;

    #[Literal(XmlProcessingInstructionTokenMatcher::class, in: XmlMode::Text)]
    case ProcessingInstruction;

    #[Literal(XmlCommentTokenMatcher::class, in: XmlMode::Text)]
    case Comment;

    #[Literal(XmlCdataTokenMatcher::class, in: XmlMode::Text)]
    case Cdata;

    #[Symbol('</', in: XmlMode::Text, enter: XmlMode::Tag)]
    case CloseTagOpen;

    #[Symbol('<', in: XmlMode::Text, enter: XmlMode::Tag)]
    case OpenTag;

    #[Symbol('/>', in: XmlMode::Tag, enter: XmlMode::Text)]
    case EmptyTagClose;

    #[Symbol('>', in: XmlMode::Tag, enter: XmlMode::Text)]
    case TagClose;

    #[Symbol('=', in: XmlMode::Tag)]
    case Equals;

    #[Literal(XmlNameTokenMatcher::class, in: XmlMode::Tag, group: TokenGroup::Identifier)]
    case Name;

    #[Literal(StringTokenMatcher::class, in: XmlMode::Tag)]
    case StringLiteral;

    #[Trivia(WhitespaceTokenMatcher::class, in: XmlMode::Tag)]
    case Whitespace;

    #[Unknown]
    case Unknown;

    #[EndOfFile]
    case EndOfFile;
}
