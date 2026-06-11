<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\Attributes\Many;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class ManyParslet implements ParsletInterface
{
    public function __construct(private Many $many)
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): object
    {
        $items = $parser->many(
            fn (Parser $parser): ?object => $parser->parseManyNode($this->many->node)
        );

        return $nodeClass->newInstance($items);
    }
}
