<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\Sequence;
use Lexicon\Parser\Part;

#[Sequence([
    [Part::Optional, AttributeIntegerNode::class],
    ExpressionTokenType::Plus,
])]
final readonly class AttributePartOptionalIntegerNode
{
    public function __construct(
        public ?AttributeIntegerNode $number,
        public Token $plus
    ) {
    }
}
