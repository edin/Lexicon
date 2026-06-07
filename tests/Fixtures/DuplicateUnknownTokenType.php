<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Unknown;

enum DuplicateUnknownTokenType
{
    #[Unknown]
    case FirstUnknown;

    #[Unknown]
    case SecondUnknown;

    #[EndOfFile]
    case EndOfFile;
}
