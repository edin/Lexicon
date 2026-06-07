<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Lexer\Token;
use Lexicon\Parser\ParseableNodeInterface;
use Lexicon\Parser\Parser;

final readonly class IntegerNode implements ExpressionNodeInterface, ParseableNodeInterface
{
    public function __construct(public Token $token)
    {
    }

    public static function parse(Parser $parser): static
    {
        return new self($parser->expect(ExpressionTokenType::Integer, 'Expected integer.'));
    }
}
