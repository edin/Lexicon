<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\Fold;

#[Fold(
    operators: [ExpressionTokenType::Plus],
    operand: IntegerNode::class
)]
final readonly class AddExpressionNode implements ExpressionNodeInterface
{
    public function __construct(
        public Token $operator,
        public ExpressionNodeInterface $left,
        public ExpressionNodeInterface $right
    )
    {
    }
}
