<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Attributes\Optional;

#[Optional(AttributeIntegerNode::class)]
final readonly class AttributeOptionalIntegerNode
{
    public function __construct(public ?AttributeIntegerNode $node)
    {
    }
}
