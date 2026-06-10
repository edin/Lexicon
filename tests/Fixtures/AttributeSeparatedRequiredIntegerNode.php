<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Attributes\SeparatedByRequired;

#[SeparatedByRequired(
    AttributeIntegerNode::class,
    ExpressionTokenType::Comma,
    allowTrailingSeparator: true
)]
final readonly class AttributeSeparatedRequiredIntegerNode
{
    /**
     * @param non-empty-list<AttributeIntegerNode> $items
     */
    public function __construct(public array $items)
    {
    }
}
