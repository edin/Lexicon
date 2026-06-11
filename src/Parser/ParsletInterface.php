<?php

declare(strict_types=1);

namespace Lexicon\Parser;

use ReflectionClass;

interface ParsletInterface
{
    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): ?object;
}
