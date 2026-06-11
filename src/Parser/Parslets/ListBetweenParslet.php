<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\Attributes\ListBetween;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class ListBetweenParslet implements ParsletInterface
{
    public function __construct(private ListBetween $listBetween)
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): ?object
    {
        if (!$report && !$parser->tokens->check($this->listBetween->open)) {
            return null;
        }

        $items = $parser->listBetween(
            $this->listBetween->open,
            fn (Parser $parser): ?object => $parser->parseNode($this->listBetween->item, report: false),
            $this->listBetween->separator,
            $this->listBetween->close,
            $this->listBetween->allowTrailingSeparator,
            $this->listBetween->openMessage,
            $this->listBetween->closeMessage
        );

        return $nodeClass->newInstance($items);
    }
}
