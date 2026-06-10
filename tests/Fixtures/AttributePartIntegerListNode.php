<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Attributes\Sequence;
use Lexicon\Parser\Part;

#[Sequence([
    [Part::ListBetween, AttributeIntegerNode::class, ExpressionTokenType::Comma, ExpressionTokenType::OpenParen, ExpressionTokenType::CloseParen, true],
])]
final readonly class AttributePartIntegerListNode
{
    /**
     * @param list<AttributeIntegerNode> $items
     */
    public function __construct(public array $items)
    {
    }
}
