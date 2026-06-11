<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\Parser;
use Lexicon\Parser\ParserAttributeInterface;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class ParserAttributeParslet implements ParsletInterface
{
    public function __construct(private ParserAttributeInterface $attribute)
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): ?object
    {
        return $this->attribute->parse($parser, $nodeClass, $report);
    }
}
