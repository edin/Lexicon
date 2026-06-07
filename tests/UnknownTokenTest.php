<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\SourceFile;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenGroup;
use Lexicon\Tests\Fixtures\TestTokenType;
use Lexicon\Tests\Support\TokenTestHelpers;
use PHPUnit\Framework\TestCase;

final class UnknownTokenTest extends TestCase
{
    use TokenTestHelpers;

    public function testUnknownCharactersUseAttributeDefinedUnknownToken(): void
    {
        $lexer = Lexer::from(TestTokenType::class);
        $tokens = $lexer->scan(new SourceFile('test.cx', 'if @@@ value'));

        self::assertSame(
            [TestTokenType::IfKeyword, TestTokenType::Unknown, TestTokenType::Identifier, TestTokenType::EndOfFile],
            array_map(fn (Token $token): TestTokenType => $token->type, $tokens)
        );
        self::assertSame(TokenGroup::Unknown, $tokens[1]->group);
        self::assertSame('@@@', $tokens[1]->value);
        self::assertSame([TestTokenType::Whitespace], array_map(fn (Token $token): TestTokenType => $token->type, $tokens[1]->leadingTrivia));
        self::assertTrue($lexer->diagnostics->hasErrors());
        self::assertSame('if @@@ value', self::reconstruct($tokens));
    }

    public function testUnknownBatchStopsBeforeKnownToken(): void
    {
        $tokens = self::tokenize('@@@if');

        self::assertSame(
            [TestTokenType::Unknown, TestTokenType::IfKeyword, TestTokenType::EndOfFile],
            array_map(fn (Token $token): TestTokenType => $token->type, $tokens)
        );
        self::assertSame('@@@', $tokens[0]->value);
    }
}
