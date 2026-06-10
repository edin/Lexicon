<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\PrefixMany;
use Lexicon\Parser\Attributes\Sequence;

#[PrefixMany(AttributeIntegerNode::class)]
#[Sequence([
    [ExpressionTokenType::Plus, ExpressionTokenType::Minus],
    AttributeIntegerNode::class,
])]
final readonly class AttributePrefixedSignedIntegerNode
{
    /**
     * @param list<AttributeIntegerNode> $prefixes
     */
    public function __construct(
        public array $prefixes,
        public Token $sign,
        public AttributeIntegerNode $number
    )
    {
    }
}
