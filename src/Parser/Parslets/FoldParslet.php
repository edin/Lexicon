<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\Fold;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class FoldParslet implements ParsletInterface
{
    public function __construct(private Fold $fold)
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): object
    {
        return $parser->fold(
            $this->fold->operators,
            fn (Parser $parser): object => $parser->parse($this->fold->operand),
            fn (Token $operator, object $left, object $right): object => $nodeClass->newInstance($operator, $left, $right),
            $this->fold->associativity
        );
    }
}
