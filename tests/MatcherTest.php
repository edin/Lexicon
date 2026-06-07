<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Tests\Fixtures\DecimalTokenType;
use Lexicon\Tests\Fixtures\IntegerTokenType;
use Lexicon\Tests\Fixtures\RegexTokenType;
use Lexicon\Tests\Fixtures\WordTokenType;
use PHPUnit\Framework\TestCase;

final class MatcherTest extends TestCase
{
    public function testIntegerMatcher(): void
    {
        $tokens = Lexer::from(IntegerTokenType::class)->scan('123 45_000');

        self::assertSame(
            [IntegerTokenType::Integer, IntegerTokenType::Integer, IntegerTokenType::EndOfFile],
            array_map(fn (Token $token): \UnitEnum => $token->type, $tokens)
        );
        self::assertSame('45_000', $tokens[1]->value);
    }

    public function testDecimalMatcher(): void
    {
        $tokens = Lexer::from(DecimalTokenType::class)->scan('12.5 .75 10.');

        self::assertSame(
            [DecimalTokenType::Decimal, DecimalTokenType::Decimal, DecimalTokenType::Decimal, DecimalTokenType::EndOfFile],
            array_map(fn (Token $token): \UnitEnum => $token->type, $tokens)
        );
        self::assertSame(['12.5', '.75', '10.'], array_map(fn (Token $token): string => $token->value, array_slice($tokens, 0, 3)));
    }

    public function testRegexMatcher(): void
    {
        $tokens = Lexer::from(RegexTokenType::class)->scan('@route @name_123');

        self::assertSame(
            [RegexTokenType::AttributeName, RegexTokenType::AttributeName, RegexTokenType::EndOfFile],
            array_map(fn (Token $token): \UnitEnum => $token->type, $tokens)
        );
    }

    public function testWordMatcher(): void
    {
        $tokens = Lexer::from(WordTokenType::class)->scan('hello world123');

        self::assertSame(
            [WordTokenType::Word, WordTokenType::Word, WordTokenType::Unknown, WordTokenType::EndOfFile],
            array_map(fn (Token $token): \UnitEnum => $token->type, $tokens)
        );
        self::assertSame('hello', $tokens[0]->value);
        self::assertSame('world', $tokens[1]->value);
        self::assertSame('123', $tokens[2]->value);
    }
}
