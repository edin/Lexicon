<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\Attributes\Between;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class BetweenParslet implements ParsletInterface
{
    public function __construct(private Between $between)
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): ?object
    {
        if (!$report && !$parser->tokens->check($this->between->open)) {
            return null;
        }

        $node = $parser->between(
            $this->between->open,
            fn (Parser $parser): object => $parser->parse($this->between->node),
            $this->between->close,
            $this->between->openMessage,
            $this->between->closeMessage
        );

        return $nodeClass->newInstance($node);
    }
}
