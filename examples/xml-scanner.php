<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Literal;
use Lexicon\Lexer\Attributes\Symbol;
use Lexicon\Lexer\Attributes\Trivia;
use Lexicon\Lexer\Attributes\Unknown;
use Lexicon\Lexer\Debug\TokenTable;
use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Matchers\StringTokenMatcher;
use Lexicon\Lexer\Matchers\WhitespaceTokenMatcher;
use Lexicon\Lexer\Matchers\XmlCdataTokenMatcher;
use Lexicon\Lexer\Matchers\XmlCommentTokenMatcher;
use Lexicon\Lexer\Matchers\XmlNameTokenMatcher;
use Lexicon\Lexer\Matchers\XmlProcessingInstructionTokenMatcher;
use Lexicon\Lexer\Matchers\XmlTextTokenMatcher;
use Lexicon\Lexer\TokenGroup;

enum XmlExampleMode
{
    case Text;
    case Tag;
}

enum XmlExampleToken
{
    #[Literal(XmlTextTokenMatcher::class, in: XmlExampleMode::Text)]
    case Text;

    #[Literal(XmlProcessingInstructionTokenMatcher::class, in: XmlExampleMode::Text)]
    case ProcessingInstruction;

    #[Literal(XmlCommentTokenMatcher::class, in: XmlExampleMode::Text)]
    case Comment;

    #[Literal(XmlCdataTokenMatcher::class, in: XmlExampleMode::Text)]
    case Cdata;

    #[Symbol('</', in: XmlExampleMode::Text, enter: XmlExampleMode::Tag)]
    case CloseTagOpen;

    #[Symbol('<', in: XmlExampleMode::Text, enter: XmlExampleMode::Tag)]
    case OpenTag;

    #[Symbol('/>', in: XmlExampleMode::Tag, enter: XmlExampleMode::Text)]
    case EmptyTagClose;

    #[Symbol('>', in: XmlExampleMode::Tag, enter: XmlExampleMode::Text)]
    case TagClose;

    #[Symbol('=', in: XmlExampleMode::Tag)]
    case Equals;

    #[Literal(XmlNameTokenMatcher::class, in: XmlExampleMode::Tag, group: TokenGroup::Identifier)]
    case Name;

    #[Literal(StringTokenMatcher::class, in: XmlExampleMode::Tag)]
    case StringLiteral;

    #[Trivia(WhitespaceTokenMatcher::class, in: XmlExampleMode::Tag)]
    case Whitespace;

    #[Unknown]
    case Unknown;

    #[EndOfFile]
    case EndOfFile;
}

$source = '<?xml version="1.0"?><note id="7">Hello <b>world</b><![CDATA[<raw>]]></note>';

$tokens = Lexer::from(XmlExampleToken::class)
    ->startIn(XmlExampleMode::Text)
    ->scan($source);

echo TokenTable::format($tokens, color: true) . PHP_EOL;
