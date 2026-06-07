<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Matchers;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenMetadata;

final class LineCommentTokenMatcher implements TokenMatcherInterface
{
    public function __construct(private readonly TokenMetadata $metadata)
    {
    }

    public function match(Lexer $lexer): ?Token
    {
        $location = $lexer->location();

        if (!$lexer->tryTake('//')) {
            return null;
        }

        $value = '//' . $lexer->takeWhile(fn (string $char): bool => $char !== "\r" && $char !== "\n");

        return new Token($this->metadata->type, $value, $location, $this->metadata->group);
    }
}
