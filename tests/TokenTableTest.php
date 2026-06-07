<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Lexer\Debug\TokenTable;
use Lexicon\Tests\Support\TokenTestHelpers;
use PHPUnit\Framework\TestCase;

final class TokenTableTest extends TestCase
{
    use TokenTestHelpers;

    public function testTokenTableFormatsTokens(): void
    {
        $tokens = self::tokenize("if\nvalue");
        $table = TokenTable::format($tokens);

        self::assertStringContainsString('Kind', $table);
        self::assertStringContainsString('Mode', $table);
        self::assertStringContainsString('IfKeyword', $table);
        self::assertStringContainsString('Whitespace', $table);
        self::assertStringContainsString('\n', $table);
        self::assertStringContainsString('Identifier', $table);
        self::assertStringContainsString('EndOfFile', $table);
    }

    public function testTokenTableCanHideTrivia(): void
    {
        $tokens = self::tokenize("if\nvalue");
        $table = TokenTable::format($tokens, includeTrivia: false);

        self::assertStringContainsString('IfKeyword', $table);
        self::assertStringNotContainsString('Whitespace', $table);
    }

    public function testTokenTableShrinksLongValues(): void
    {
        $tokens = self::tokenize('if very_long_identifier_name');
        $table = TokenTable::format($tokens, maxValueLength: 10);

        self::assertStringContainsString('very_lo...', $table);
        self::assertStringNotContainsString('very_long_identifier_name', $table);
    }

    public function testTokenTableCanColorKeywords(): void
    {
        $tokens = self::tokenize('if value');
        $table = TokenTable::format($tokens, color: true);

        self::assertStringContainsString("\033[36mIfKeyword\033[0m", $table);
        self::assertStringContainsString("\033[36mKeyword\033[0m", $table);
        self::assertStringNotContainsString("\033[36mIdentifier\033[0m", $table);
    }
}
