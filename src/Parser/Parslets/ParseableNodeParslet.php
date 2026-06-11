<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\ParseableNodeInterface;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class ParseableNodeParslet implements ParsletInterface
{
    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): object
    {
        /** @var class-string<ParseableNodeInterface> $class */
        $class = $nodeClass->getName();

        return $class::parse($parser);
    }
}
