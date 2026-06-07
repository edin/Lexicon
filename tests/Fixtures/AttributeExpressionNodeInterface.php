<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Attributes\OneOf;

#[OneOf([
    AttributeGroupedIntegerNode::class,
    AttributeIntegerNode::class,
])]
interface AttributeExpressionNodeInterface
{
}
