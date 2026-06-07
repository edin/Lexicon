<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Attributes\ListBetween;

#[ListBetween(
    ExpressionTokenType::OpenParen,
    AttributeIntegerNode::class,
    ExpressionTokenType::Comma,
    ExpressionTokenType::CloseParen,
    allowTrailingSeparator: true
)]
final readonly class AttributeIntegerListNode
{
    /**
     * @param list<AttributeIntegerNode> $items
     */
    public function __construct(public array $items)
    {
    }
}
