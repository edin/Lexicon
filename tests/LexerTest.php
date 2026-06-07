<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\SourceFile;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenGroup;
use Lexicon\Lexer\TokenMetadataProvider;
use Lexicon\Tests\Fixtures\TestTokenType;
use Lexicon\Tests\Support\TokenTestHelpers;
use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    use TokenTestHelpers;

    public function testTokenizeUsesLongestSymbolMatchFromMetadata(): void
    {
        $tokens = self::tokenize('... .. . <=> <= < => -> -');

        self::assertSame(
            [
                TestTokenType::Ellipsis,
                TestTokenType::DotDot,
                TestTokenType::Dot,
                TestTokenType::Spaceship,
                TestTokenType::LessThanOrEqual,
                TestTokenType::LessThan,
                TestTokenType::FatArrow,
                TestTokenType::Arrow,
                TestTokenType::Minus,
                TestTokenType::EndOfFile,
            ],
            array_map(fn (Token $token): TestTokenType => $token->type, $tokens)
        );
    }

    public function testTokenizeCoercesIdentifiersToKnownKeywords(): void
    {
        $tokens = self::tokenize('if ifx foreach foreach_value true true_value');

        self::assertSame(
            [
                TestTokenType::IfKeyword,
                TestTokenType::Identifier,
                TestTokenType::ForeachKeyword,
                TestTokenType::Identifier,
                TestTokenType::TrueKeyword,
                TestTokenType::Identifier,
                TestTokenType::EndOfFile,
            ],
            array_map(fn (Token $token): TestTokenType => $token->type, $tokens)
        );
    }

    public function testTokenizeUsesMatcherTokensBeforeSymbolTokens(): void
    {
        $tokens = self::tokenize("// comment\n/ \"text\" 'c' 123");

        self::assertSame(
            [
                TestTokenType::Slash,
                TestTokenType::StringLiteral,
                TestTokenType::CharacterLiteral,
                TestTokenType::Number,
                TestTokenType::EndOfFile,
            ],
            array_map(fn (Token $token): TestTokenType => $token->type, $tokens)
        );

        self::assertSame(
            [TestTokenType::Comment, TestTokenType::Whitespace],
            array_map(fn (Token $token): TestTokenType => $token->type, $tokens[0]->leadingTrivia)
        );
    }

    public function testMetadataComesFromTokenAttributes(): void
    {
        $metadata = TokenMetadataProvider::for(TestTokenType::class)->byType()[TestTokenType::IfKeyword->name];

        self::assertSame(TestTokenType::IfKeyword, $metadata->type);
        self::assertSame('if', $metadata->text);
        self::assertSame(TokenGroup::Keyword, $metadata->group);
    }

    public function testScannerCanBeCreatedFromTokenEnumType(): void
    {
        $tokens = Lexer::from(TestTokenType::class)->scan('if value');

        self::assertSame(
            [TestTokenType::IfKeyword, TestTokenType::Identifier, TestTokenType::EndOfFile],
            array_map(fn (Token $token): \UnitEnum => $token->type, $tokens)
        );
    }

    public function testDiagnosticsAreCreatedForEachScan(): void
    {
        $lexer = Lexer::from(TestTokenType::class);

        $lexer->scan('#');
        self::assertTrue($lexer->diagnostics->hasErrors());

        $lexer->scan('if');
        self::assertFalse($lexer->diagnostics->hasErrors());
    }

    public function testTokenDebugInfoDoesNotDumpSourceText(): void
    {
        $tokens = Lexer::from(TestTokenType::class)->scan(new SourceFile('debug.cx', 'if hidden_source_text'));
        $debug = print_r($tokens[0], true);

        self::assertStringContainsString('[type] => IfKeyword', $debug);
        self::assertStringContainsString('[file] => debug.cx', $debug);
        self::assertStringContainsString('[span]', $debug);
        self::assertStringNotContainsString('hidden_source_text', $debug);
    }
}
