<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\Attributes\Sequence;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class SequenceAlternativesParslet implements ParsletInterface
{
    /**
     * @param list<\ReflectionAttribute<Sequence>> $sequenceAttributes
     * @param list<mixed> $prefixValues
     */
    public function __construct(
        private array $sequenceAttributes,
        private array $prefixValues = []
    ) {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): ?object
    {
        return $parser->parseSequenceAlternatives($nodeClass, $this->sequenceAttributes, $report, $this->prefixValues);
    }
}
