<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Tests\Fixtures\XmlMode;
use Lexicon\Tests\Fixtures\XmlTokenType;
use PHPUnit\Framework\TestCase;

final class XmlLexerTest extends TestCase
{
    public function testXmlDedicatedMatchersHandleSpecialRegions(): void
    {
        $tokens = Lexer::from(XmlTokenType::class)
            ->startIn(XmlMode::Text)
            ->scan('<?xml version="1.0"?><!--c--><![CDATA[<raw>]]><br/>');

        self::assertSame(
            [
                XmlTokenType::ProcessingInstruction,
                XmlTokenType::Comment,
                XmlTokenType::Cdata,
                XmlTokenType::OpenTag,
                XmlTokenType::Name,
                XmlTokenType::EmptyTagClose,
                XmlTokenType::EndOfFile,
            ],
            array_map(fn (Token $token): \UnitEnum => $token->type, $tokens)
        );
        self::assertSame('<?xml version="1.0"?>', $tokens[0]->value);
        self::assertSame('<!--c-->', $tokens[1]->value);
        self::assertSame('<![CDATA[<raw>]]>', $tokens[2]->value);
    }
}
