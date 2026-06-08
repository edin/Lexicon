<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\Sequence;

#[Sequence([
    [ExpressionTokenType::Plus, ExpressionTokenType::Minus],
    AttributeIntegerNode::class,
])]
final readonly class AttributeSignedIntegerNode
{
    public function __construct(
        public Token $sign,
        public AttributeIntegerNode $number
    )
    {
    }
}
