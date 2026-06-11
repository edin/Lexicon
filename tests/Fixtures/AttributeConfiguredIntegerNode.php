<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\Parse;

#[Parse(IntegerParslet::class, ['Expected configured integer.'])]
final readonly class AttributeConfiguredIntegerNode
{
    public function __construct(public Token $token)
    {
    }
}
