<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Attributes\Many;

#[Many(AttributeIntegerNode::class)]
final readonly class AttributeManyIntegerNode
{
    /**
     * @param list<AttributeIntegerNode> $items
     */
    public function __construct(public array $items)
    {
    }
}
