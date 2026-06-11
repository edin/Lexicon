<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\Attributes\Terminal;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class TerminalParslet implements ParsletInterface
{
    public function __construct(private Terminal $terminal)
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): ?object
    {
        $match = $report
            ? $parser->expect($this->terminal->type, $this->terminal->message)
            : $parser->tokens->match($this->terminal->type);

        return $match === null ? null : $nodeClass->newInstance($match);
    }
}
