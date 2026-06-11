<?php

declare(strict_types=1);

namespace Lexicon\Parser;

use Lexicon\Parser\Parslets\DispatchedParslet;
use Lexicon\Parser\Parslets\ParseableNodeParslet;
use Lexicon\Parser\Parslets\ParserAttributeParslet;
use ReflectionClass;
use UnitEnum;

final readonly class DefaultParsletFactory implements ParsletFactoryInterface
{
    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function forNode(ReflectionClass $nodeClass): ?ParsletInterface
    {
        foreach ($nodeClass->getAttributes() as $attribute) {
            $parslet = $this->forAttribute($attribute->newInstance());
            if ($parslet !== null) {
                return $parslet;
            }
        }

        foreach ($nodeClass->getAttributes() as $attribute) {
            $provider = $attribute->newInstance();
            if (!$provider instanceof NodeParsletProviderInterface) {
                continue;
            }

            $parslet = $provider->parsletForNode($nodeClass, $this);
            if ($parslet !== null) {
                return $parslet;
            }
        }

        if (is_subclass_of($nodeClass->getName(), ParseableNodeInterface::class)) {
            return new ParseableNodeParslet();
        }

        return null;
    }

    public function forAttribute(object $attribute): ?ParsletInterface
    {
        if ($attribute instanceof ParsletProviderInterface) {
            return $attribute->parslet($this);
        }

        if ($attribute instanceof ParserAttributeInterface) {
            return new ParserAttributeParslet($attribute);
        }

        return null;
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public function forDefinition(mixed $definition, array $arguments = []): ?ParsletInterface
    {
        if ($definition instanceof ParsletInterface) {
            return $definition;
        }

        if (is_string($definition) && is_subclass_of($definition, ParsletInterface::class)) {
            return $this->forClass($definition, $arguments);
        }

        if (is_array($definition) && ($definition[0] ?? null) instanceof UnitEnum) {
            $dispatch = $definition[0];
            if ($dispatch instanceof ParsletDispatchInterface) {
                return new DispatchedParslet($dispatch, array_merge(array_slice($definition, 1), $arguments));
            }
        }

        return null;
    }

    /**
     * @param class-string<ParsletInterface> $parsletClass
     * @param array<array-key, mixed> $arguments
     */
    public function forClass(string $parsletClass, array $arguments = []): ParsletInterface
    {
        return new $parsletClass(...$arguments);
    }
}
