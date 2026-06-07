<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Matchers;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenMetadata;

final readonly class TextTokenMatcher implements ITokenMatcher
{
    public function __construct(private TokenMetadata $metadata)
    {
    }

    public function match(Lexer $lexer): ?Token
    {
        $location = $lexer->location();

        if (!$lexer->tryTake($this->metadata->text ?? '')) {
            return null;
        }

        return new Token($this->metadata->type, $this->metadata->text ?? '', $location, $this->metadata->group);
    }
}
