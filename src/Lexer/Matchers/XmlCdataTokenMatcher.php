<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Matchers;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenMetadata;

final class XmlCdataTokenMatcher implements ITokenMatcher
{
    public function __construct(private readonly TokenMetadata $metadata)
    {
    }

    public function match(Lexer $lexer): ?Token
    {
        $location = $lexer->location();

        if (!$lexer->tryTake('<![CDATA[')) {
            return null;
        }

        $value = '<![CDATA[' . $lexer->takeUntil(']]>');

        if ($lexer->tryTake(']]>')) {
            $value .= ']]>';
        } else {
            $lexer->diagnostics->report($location, 'Unterminated XML CDATA section.');
        }

        return new Token($this->metadata->type, $value, $location, $this->metadata->group);
    }
}
