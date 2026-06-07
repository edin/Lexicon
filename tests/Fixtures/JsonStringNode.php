<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;

final readonly class JsonStringNode implements JsonNodeInterface
{
    public function __construct(public Token $token)
    {
    }
}
