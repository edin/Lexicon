<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\Sequence;

#[Sequence([AttributeIntegerNode::class], factory: 'integer')]
#[Sequence([
    [ExpressionTokenType::Plus, ExpressionTokenType::Minus],
    AttributeIntegerNode::class,
], factory: 'signed')]
final readonly class AttributeIntegerOrSignedIntegerNode
{
    private function __construct(
        public AttributeIntegerNode $number,
        public ?Token $sign
    ) {
    }

    public static function integer(AttributeIntegerNode $number): self
    {
        return new self($number, null);
    }

    public static function signed(Token $sign, AttributeIntegerNode $number): self
    {
        return new self($number, $sign);
    }
}
