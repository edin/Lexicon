<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Matchers;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenMetadata;
use LogicException;

final class RegexTokenMatcher implements TokenMatcherInterface
{
    public function __construct(private readonly TokenMetadata $metadata)
    {
        if ($metadata->text === null || $metadata->text === '') {
            throw new LogicException('Regex token matcher requires a non-empty pattern.');
        }
    }

    public function match(Lexer $lexer): ?Token
    {
        $pattern = $this->metadata->text;
        if (!preg_match($pattern, $lexer->remaining(), $match, PREG_OFFSET_CAPTURE) || $match[0][1] !== 0) {
            return null;
        }

        $value = $match[0][0];
        if ($value === '') {
            return null;
        }

        $location = $lexer->location();
        for ($i = 0, $length = strlen($value); $i < $length; $i++) {
            $lexer->advance();
        }

        return new Token($this->metadata->type, $value, $location, $this->metadata->group);
    }
}
