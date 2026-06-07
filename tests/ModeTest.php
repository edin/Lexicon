<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Tests\Fixtures\StackMode;
use Lexicon\Tests\Fixtures\StackModeTokenType;
use Lexicon\Tests\Fixtures\XmlMode;
use Lexicon\Tests\Fixtures\XmlTokenType;
use Lexicon\Tests\Support\TokenTestHelpers;
use PHPUnit\Framework\TestCase;

final class ModeTest extends TestCase
{
    use TokenTestHelpers;

    public function testLexerModesCanTokenizeXmlLikeInput(): void
    {
        $tokens = Lexer::from(XmlTokenType::class)
            ->startIn(XmlMode::Text)
            ->scan('<note id="7">Hi</note>');

        self::assertSame(
            [
                XmlTokenType::OpenTag,
                XmlTokenType::Name,
                XmlTokenType::Name,
                XmlTokenType::Equals,
                XmlTokenType::StringLiteral,
                XmlTokenType::TagClose,
                XmlTokenType::Text,
                XmlTokenType::CloseTagOpen,
                XmlTokenType::Name,
                XmlTokenType::TagClose,
                XmlTokenType::EndOfFile,
            ],
            array_map(fn (Token $token): \UnitEnum => $token->type, $tokens)
        );

        self::assertSame([XmlTokenType::Whitespace], array_map(fn (Token $token): \UnitEnum => $token->type, $tokens[2]->leadingTrivia));
        self::assertSame(XmlMode::Text, $tokens[0]->mode);
        self::assertSame(XmlMode::Tag, $tokens[1]->mode);
        self::assertSame(XmlMode::Text, $tokens[6]->mode);
        self::assertSame('<note id="7">Hi</note>', self::reconstruct($tokens));
    }

    public function testLexerModeStackCanPushAndPopModes(): void
    {
        $tokens = Lexer::from(StackModeTokenType::class)
            ->startIn(StackMode::Code)
            ->scan('name "hello" tail');

        self::assertSame(
            [
                StackModeTokenType::Identifier,
                StackModeTokenType::StringStart,
                StackModeTokenType::StringText,
                StackModeTokenType::StringEnd,
                StackModeTokenType::Identifier,
                StackModeTokenType::EndOfFile,
            ],
            array_map(fn (Token $token): \UnitEnum => $token->type, $tokens)
        );

        self::assertSame(StackMode::Code, $tokens[1]->mode);
        self::assertSame(StackMode::String, $tokens[2]->mode);
        self::assertSame(StackMode::String, $tokens[3]->mode);
        self::assertSame(StackMode::Code, $tokens[4]->mode);
        self::assertSame('name "hello" tail', self::reconstruct($tokens));
    }
}
