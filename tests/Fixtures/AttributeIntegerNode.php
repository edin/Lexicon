<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token as LexerToken;
use Lexicon\Parser\Attributes\Terminal;

#[Terminal(ExpressionTokenType::Integer, 'Expected integer.')]
final readonly class AttributeIntegerNode implements AttributeExpressionNodeInterface
{
    public function __construct(public LexerToken $token)
    {
    }
}
