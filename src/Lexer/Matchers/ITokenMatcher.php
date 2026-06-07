<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Matchers;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;

interface ITokenMatcher
{
    public function match(Lexer $lexer): ?Token;
}
