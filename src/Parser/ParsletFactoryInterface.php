<?php

declare(strict_types=1);

namespace Lexicon\Parser;

use ReflectionClass;

interface ParsletFactoryInterface
{
    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function forNode(ReflectionClass $nodeClass): ?ParsletInterface;

    public function forAttribute(object $attribute): ?ParsletInterface;

    /**
     * @param array<array-key, mixed> $arguments
     */
    public function forDefinition(mixed $definition, array $arguments = []): ?ParsletInterface;

    /**
     * @param class-string<ParsletInterface> $parsletClass
     * @param array<array-key, mixed> $arguments
     */
    public function forClass(string $parsletClass, array $arguments = []): ParsletInterface;
}
