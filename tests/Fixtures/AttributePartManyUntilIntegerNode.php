<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\Sequence;
use Lexicon\Parser\Part;

#[Sequence([
    [Part::ManyUntilRequired, AttributeIntegerNode::class, ExpressionTokenType::Plus],
    ExpressionTokenType::Plus,
])]
final readonly class AttributePartManyUntilIntegerNode
{
    /**
     * @param non-empty-list<AttributeIntegerNode> $items
     */
    public function __construct(
        public array $items,
        public Token $plus
    ) {
    }
}
