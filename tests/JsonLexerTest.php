<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Tests\Fixtures\JsonTokenType;
use PHPUnit\Framework\TestCase;

final class JsonLexerTest extends TestCase
{
    public function testJsonMatchersTokenizeJsonInput(): void
    {
        $tokens = Lexer::from(JsonTokenType::class)->scan('{"ok": true, "n": -12.5e+2, "s": "a\\n"}');

        self::assertSame(
            [
                JsonTokenType::LeftBrace,
                JsonTokenType::StringLiteral,
                JsonTokenType::Colon,
                JsonTokenType::True,
                JsonTokenType::Comma,
                JsonTokenType::StringLiteral,
                JsonTokenType::Colon,
                JsonTokenType::Number,
                JsonTokenType::Comma,
                JsonTokenType::StringLiteral,
                JsonTokenType::Colon,
                JsonTokenType::StringLiteral,
                JsonTokenType::RightBrace,
                JsonTokenType::EndOfFile,
            ],
            array_map(fn (Token $token): \UnitEnum => $token->type, $tokens)
        );
        self::assertSame('-12.5e+2', $tokens[7]->value);
        self::assertSame('"a\\n"', $tokens[11]->value);
    }

    public function testJsonStringMatcherReportsInvalidEscapes(): void
    {
        $lexer = Lexer::from(JsonTokenType::class);
        $lexer->scan('"bad\\x"');

        self::assertTrue($lexer->diagnostics->hasErrors());
        self::assertSame("Invalid JSON escape '\\x'.", $lexer->diagnostics->all()[0]->message);
    }
}
