<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\Parse;

#[Parse([TestParslet::Integer, 'Expected enum dispatched integer.'])]
final readonly class AttributeDispatchedIntegerNode
{
    public function __construct(public Token $token)
    {
    }
}
