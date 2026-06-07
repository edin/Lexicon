<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Attributes\Symbol;

enum MissingEndOfFileTokenType
{
    #[Symbol('=')]
    case Equals;
}
