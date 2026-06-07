<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Matchers;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenMetadata;

final class BlockCommentTokenMatcher implements ITokenMatcher
{
    public function __construct(private readonly TokenMetadata $metadata)
    {
    }

    public function match(Lexer $lexer): ?Token
    {
        $location = $lexer->location();

        if (!$lexer->tryTake('/*')) {
            return null;
        }

        $value = '/*' . $lexer->takeUntil('*/');

        if ($lexer->tryTake('*/')) {
            $value .= '*/';

            return new Token($this->metadata->type, $value, $location, $this->metadata->group);
        }

        $lexer->diagnostics->report($location, 'Unterminated multiline comment.');

        return new Token($this->metadata->type, $value, $location, $this->metadata->group);
    }
}
