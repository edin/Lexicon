<?php

declare(strict_types=1);

namespace Lexicon\Parser;

use ReflectionClass;

interface ParsletDispatchInterface
{
    /**
     * @param ReflectionClass<object> $nodeClass
     * @param list<mixed> $arguments
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report, array $arguments): ?object;
}
