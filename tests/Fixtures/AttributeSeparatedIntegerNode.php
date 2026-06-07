<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Attributes\SeparatedBy;

#[SeparatedBy(
    AttributeIntegerNode::class,
    ExpressionTokenType::Comma,
    allowTrailingSeparator: true
)]
final readonly class AttributeSeparatedIntegerNode
{
    /**
     * @param list<AttributeIntegerNode> $items
     */
    public function __construct(public array $items)
    {
    }
}
