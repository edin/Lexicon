<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Attributes\Sequence;

#[Sequence([AttributeIntegerNode::class], factory: 'decline')]
#[Sequence([AttributeIntegerNode::class], factory: 'accept')]
final readonly class AttributeDecliningFactoryIntegerNode
{
    private function __construct(public AttributeIntegerNode $number)
    {
    }

    public static function decline(AttributeIntegerNode $number): ?self
    {
        return null;
    }

    public static function accept(AttributeIntegerNode $number): self
    {
        return new self($number);
    }
}
