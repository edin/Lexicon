<?php

declare(strict_types=1);

namespace Lexicon\Parser;

use ReflectionClass;

interface NodeParsletProviderInterface
{
    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parsletForNode(ReflectionClass $nodeClass, ParsletFactoryInterface $factory): ?ParsletInterface;
}
