<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenGroup;
use Lexicon\Tests\Fixtures\TestTokenType;
use Lexicon\Tests\Support\TokenTestHelpers;
use PHPUnit\Framework\TestCase;

final class TokenTest extends TestCase
{
    use TokenTestHelpers;

    public function testTokenCapturesGroupFromMetadata(): void
    {
        $tokens = self::tokenize('if value');

        self::assertSame(TokenGroup::Keyword, $tokens[0]->group);
        self::assertSame(TokenGroup::Identifier, $tokens[1]->group);
    }

    public function testTokenSpanUsesStartPositionAndValueLength(): void
    {
        $tokens = self::tokenize('if value');

        self::assertSame(0, $tokens[0]->span()->start);
        self::assertSame(2, $tokens[0]->span()->length);
        self::assertSame(2, $tokens[0]->span()->end());

        self::assertSame(3, $tokens[1]->span()->start);
        self::assertSame(5, $tokens[1]->span()->length);
        self::assertSame(8, $tokens[1]->span()->end());
    }

    public function testLeadingTriviaTokensHaveSpans(): void
    {
        $tokens = self::tokenize("  // comment\nif");
        $trivia = $tokens[0]->leadingTrivia;

        self::assertSame(0, $trivia[0]->span()->start);
        self::assertSame(2, $trivia[0]->span()->length);
        self::assertSame(2, $trivia[0]->span()->end());

        self::assertSame(2, $trivia[1]->span()->start);
        self::assertSame(10, $trivia[1]->span()->length);
        self::assertSame(12, $trivia[1]->span()->end());
    }

    public function testTokenCapturesLeadingTriviaForLosslessReconstruction(): void
    {
        $source = "  if /* keep */ value\n// trailing";
        $tokens = self::tokenize($source);

        self::assertSame([TestTokenType::Whitespace], array_map(fn (Token $token): TestTokenType => $token->type, $tokens[0]->leadingTrivia));
        self::assertSame(
            [TestTokenType::Whitespace, TestTokenType::MultilineComment, TestTokenType::Whitespace],
            array_map(fn (Token $token): TestTokenType => $token->type, $tokens[1]->leadingTrivia)
        );
        self::assertSame(
            [TestTokenType::Whitespace, TestTokenType::Comment],
            array_map(fn (Token $token): TestTokenType => $token->type, $tokens[2]->leadingTrivia)
        );

        self::assertSame($source, self::reconstruct($tokens));
    }
}
