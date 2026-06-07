<?php

declare(strict_types=1);

namespace Lexicon\Lexer;

enum TokenGroup
{
    case Identifier;
    case Literal;
    case Keyword;
    case Symbol;
    case Trivia;
    case Unknown;
    case EndOfFile;
}
