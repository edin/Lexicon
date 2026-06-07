<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Matchers;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenMetadata;

final class IntegerTokenMatcher implements ITokenMatcher
{
    public function __construct(private readonly TokenMetadata $metadata)
    {
    }

    public function match(Lexer $lexer): ?Token
    {
        if ($lexer->isAtEnd() || !ctype_digit($lexer->current())) {
            return null;
        }

        $location = $lexer->location();
        $value = $lexer->takeWhile(fn (string $char): bool => ctype_digit($char) || $char === '_');

        return new Token($this->metadata->type, $value, $location, $this->metadata->group);
    }
}
