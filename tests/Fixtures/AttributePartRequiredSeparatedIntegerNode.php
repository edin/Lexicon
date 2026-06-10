<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Attributes\Sequence;
use Lexicon\Parser\Part;

#[Sequence([
    [Part::SeparatedByRequired, AttributeIntegerNode::class, ExpressionTokenType::Comma],
])]
final readonly class AttributePartRequiredSeparatedIntegerNode
{
    /**
     * @param non-empty-list<AttributeIntegerNode> $items
     */
    public function __construct(public array $items)
    {
    }
}
