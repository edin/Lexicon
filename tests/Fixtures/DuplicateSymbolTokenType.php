<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Symbol;

enum DuplicateSymbolTokenType
{
    #[Symbol('=')]
    case Equals;

    #[Symbol('=')]
    case Assign;

    #[EndOfFile]
    case EndOfFile;
}
