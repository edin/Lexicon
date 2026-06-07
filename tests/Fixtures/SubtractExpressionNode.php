<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;

final readonly class SubtractExpressionNode implements ExpressionNodeInterface
{
    public function __construct(
        public Token $operator,
        public ExpressionNodeInterface $left,
        public ExpressionNodeInterface $right
    )
    {
    }
}
