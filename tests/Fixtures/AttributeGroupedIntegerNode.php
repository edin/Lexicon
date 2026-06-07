<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Attributes\Between;

#[Between(
    ExpressionTokenType::OpenParen,
    AttributeIntegerNode::class,
    ExpressionTokenType::CloseParen
)]
final readonly class AttributeGroupedIntegerNode implements AttributeExpressionNodeInterface
{
    public function __construct(public AttributeIntegerNode $node)
    {
    }
}
