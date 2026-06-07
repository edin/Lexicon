<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Matchers;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenMetadata;

final class WordTokenMatcher implements TokenMatcherInterface
{
    public function __construct(private readonly TokenMetadata $metadata)
    {
    }

    public function match(Lexer $lexer): ?Token
    {
        if ($lexer->isAtEnd() || !ctype_alpha($lexer->current())) {
            return null;
        }

        $location = $lexer->location();
        $value = $lexer->takeWhile(fn (string $char): bool => ctype_alpha($char));

        return new Token($this->metadata->type, $value, $location, $this->metadata->group);
    }
}
