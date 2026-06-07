<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Matchers;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenMetadata;

final class CharacterTokenMatcher implements ITokenMatcher
{
    public function __construct(private readonly TokenMetadata $metadata)
    {
    }

    public function match(Lexer $lexer): ?Token
    {
        if ($lexer->isAtEnd() || $lexer->current() !== "'") {
            return null;
        }

        $location = $lexer->location();
        $value = $lexer->current();
        $lexer->advance();

        while (!$lexer->isAtEnd()) {
            if ($lexer->current() === '\\') {
                $value .= $lexer->current();
                $lexer->advance();

                if (!$lexer->isAtEnd()) {
                    $value .= $lexer->current();
                    $lexer->advance();
                }

                continue;
            }

            if ($lexer->current() === "'") {
                $value .= $lexer->current();
                $lexer->advance();

                return new Token($this->metadata->type, $value, $location, $this->metadata->group);
            }

            $value .= $lexer->current();
            $lexer->advance();
        }

        $lexer->diagnostics->report($location, 'Unterminated character literal.');

        return new Token($this->metadata->type, $value, $location, $this->metadata->group);
    }
}
