<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\Attributes\SeparatedBy;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class SeparatedByParslet implements ParsletInterface
{
    public function __construct(private SeparatedBy $separatedBy)
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): object
    {
        $items = $parser->separatedBy(
            fn (Parser $parser): ?object => $parser->parseNode($this->separatedBy->node, report: false),
            $this->separatedBy->separator,
            $this->separatedBy->allowTrailingSeparator
        );

        return $nodeClass->newInstance($items);
    }
}
