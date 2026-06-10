<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\Sequence;
use Lexicon\Parser\Part;

#[Sequence([
    [Part::OptionalSequence, [ExpressionTokenType::Plus, ExpressionTokenType::Minus], AttributeIntegerNode::class],
    ExpressionTokenType::Comma,
])]
final readonly class AttributePartOptionalSignedIntegerSequenceNode
{
    /**
     * @param array{0: Token, 1: AttributeIntegerNode}|null $signed
     */
    public function __construct(
        public ?array $signed,
        public Token $comma
    ) {
    }
}
