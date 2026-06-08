<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\Sequence;

#[Sequence([
    ExpressionTokenType::OpenParen,
    AttributeIntegerNode::class,
    ExpressionTokenType::CloseParen,
])]
final readonly class AttributeGroupedSequenceNode
{
    public function __construct(
        public Token $open,
        public AttributeIntegerNode $node,
        public Token $close
    )
    {
    }
}
