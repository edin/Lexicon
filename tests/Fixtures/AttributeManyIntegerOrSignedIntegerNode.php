<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Attributes\Many;

#[Many([AttributeIntegerNode::class, AttributeSignedIntegerNode::class])]
final readonly class AttributeManyIntegerOrSignedIntegerNode
{
    /**
     * @param list<object> $items
     */
    public function __construct(public array $items)
    {
    }
}
