<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\Attributes\Optional;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class OptionalParslet implements ParsletInterface
{
    public function __construct(private Optional $optional)
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): object
    {
        $node = $parser->optional(
            fn (Parser $parser): ?object => $parser->parseNode($this->optional->node, report: false)
        );

        return $nodeClass->newInstance($node);
    }
}
