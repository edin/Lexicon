<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletDispatchInterface;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class DispatchedParslet implements ParsletInterface
{
    /**
     * @param list<mixed> $arguments
     */
    public function __construct(
        private ParsletDispatchInterface $dispatch,
        private array $arguments
    ) {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): ?object
    {
        return $this->dispatch->parse($parser, $nodeClass, $report, $this->arguments);
    }
}
