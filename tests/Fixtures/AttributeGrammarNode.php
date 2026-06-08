<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Attributes\Grammar;
use Lexicon\Parser\ParseableNodeInterface;
use Lexicon\Parser\Parser;

#[Grammar(
    'AttributeIntegerNode AttributeOptionalIntegerNode',
    dependencies: [AttributeIntegerNode::class, AttributeOptionalIntegerNode::class]
)]
final readonly class AttributeGrammarNode implements ParseableNodeInterface
{
    public static function parse(Parser $parser): static
    {
        return new self();
    }
}
