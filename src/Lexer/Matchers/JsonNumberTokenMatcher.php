<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Matchers;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenMetadata;

final class JsonNumberTokenMatcher implements TokenMatcherInterface
{
    public function __construct(private readonly TokenMetadata $metadata)
    {
    }

    public function match(Lexer $lexer): ?Token
    {
        if (!preg_match('/\A-?(?:0|[1-9]\d*)(?:\.\d+)?(?:[eE][+-]?\d+)?/', $lexer->remaining(), $match)) {
            return null;
        }

        $location = $lexer->location();
        $value = $match[0];

        for ($i = 0, $length = strlen($value); $i < $length; $i++) {
            $lexer->advance();
        }

        return new Token($this->metadata->type, $value, $location, $this->metadata->group);
    }
}
