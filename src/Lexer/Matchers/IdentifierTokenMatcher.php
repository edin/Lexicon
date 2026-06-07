<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Matchers;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenMetadata;

final readonly class IdentifierTokenMatcher implements TokenMatcherInterface
{
    /**
     * @param array<string, \UnitEnum> $tokenMap
     */
    public function __construct(
        private TokenMetadata $metadata,
        private array $tokenMap = [],
    )
    {
    }

    public function match(Lexer $lexer): ?Token
    {
        if ($lexer->isAtEnd() || !$this->isIdentifierStart($lexer->current())) {
            return null;
        }

        $location = $lexer->location();
        $value = $lexer->takeWhile(fn (string $char): bool => $this->isIdentifierPart($char));
        $type = $this->tokenMap[$value] ?? $this->metadata->type;

        return new Token($type, $value, $location);
    }

    private function isIdentifierStart(string $char): bool
    {
        return ctype_alpha($char) || $char === '_';
    }

    private function isIdentifierPart(string $char): bool
    {
        return ctype_alnum($char) || $char === '_';
    }
}
