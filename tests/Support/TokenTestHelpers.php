<?php

declare(strict_types=1);

namespace Lexicon\Tests\Support;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\SourceFile;
use Lexicon\Lexer\Token;
use Lexicon\Tests\Fixtures\TestTokenType;

trait TokenTestHelpers
{
    /**
     * @param list<Token> $tokens
     */
    private static function reconstruct(array $tokens): string
    {
        return implode('', array_map(fn (Token $token): string => $token->fullText(), $tokens));
    }

    /**
     * @return list<Token>
     */
    private static function tokenize(string $text): array
    {
        return Lexer::from(TestTokenType::class)->scan(new SourceFile('test.cx', $text));
    }
}
