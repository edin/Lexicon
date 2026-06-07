<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Keyword;

enum KeywordWithoutIdentifierTokenType
{
    #[Keyword('if')]
    case IfKeyword;

    #[EndOfFile]
    case EndOfFile;
}
