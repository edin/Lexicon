<?php

declare(strict_types=1);

namespace Lexicon\Parser\Debug;

use Lexicon\Parser\Attributes\Between;
use Lexicon\Parser\Attributes\Fold;
use Lexicon\Parser\Attributes\ListBetween;
use Lexicon\Parser\Attributes\Many;
use Lexicon\Parser\Attributes\OneOf;
use Lexicon\Parser\Attributes\Optional;
use Lexicon\Parser\Attributes\SeparatedBy;
use Lexicon\Parser\Attributes\Terminal;
use Lexicon\Parser\ParseableNodeInterface;
use ReflectionAttribute;
use ReflectionClass;
use UnitEnum;

final class GrammarPrinter
{
    /** @var array<class-string, true> */
    private array $visited = [];

    /** @var list<string> */
    private array $rules = [];

    public static function format(string $nodeClass): string
    {
        $printer = new self();
        $printer->visit($nodeClass);

        return implode(PHP_EOL, $printer->rules);
    }

    /**
     * @param class-string $nodeClass
     */
    private function visit(string $nodeClass): void
    {
        if (isset($this->visited[$nodeClass])) {
            return;
        }

        $this->visited[$nodeClass] = true;

        $reflection = new ReflectionClass($nodeClass);
        $this->rules[] = sprintf('%s ::= %s', $reflection->getShortName(), $this->expression($reflection));

        foreach ($this->dependencies($reflection) as $dependency) {
            $this->visit($dependency);
        }
    }

    /**
     * @param ReflectionClass<object> $reflection
     */
    private function expression(ReflectionClass $reflection): string
    {
        $oneOf = $this->attribute($reflection, OneOf::class);
        if ($oneOf instanceof OneOf) {
            return implode(' | ', array_map($this->nodeName(...), $oneOf->nodes));
        }

        $terminal = $this->attribute($reflection, Terminal::class);
        if ($terminal instanceof Terminal) {
            return $this->terminalName($terminal->type);
        }

        $between = $this->attribute($reflection, Between::class);
        if ($between instanceof Between) {
            return sprintf(
                '%s %s %s',
                $this->terminalName($between->open),
                $this->nodeName($between->node),
                $this->terminalName($between->close)
            );
        }

        $listBetween = $this->attribute($reflection, ListBetween::class);
        if ($listBetween instanceof ListBetween) {
            return sprintf(
                '%s (%s (%s %s)*)? %s',
                $this->terminalName($listBetween->open),
                $this->nodeName($listBetween->item),
                $this->terminalName($listBetween->separator),
                $this->nodeName($listBetween->item),
                $this->terminalName($listBetween->close)
            );
        }

        $optional = $this->attribute($reflection, Optional::class);
        if ($optional instanceof Optional) {
            return sprintf('%s?', $this->nodeName($optional->node));
        }

        $many = $this->attribute($reflection, Many::class);
        if ($many instanceof Many) {
            return sprintf('%s*', $this->nodeName($many->node));
        }

        $separatedBy = $this->attribute($reflection, SeparatedBy::class);
        if ($separatedBy instanceof SeparatedBy) {
            return sprintf(
                '(%s (%s %s)*)?',
                $this->nodeName($separatedBy->node),
                $this->terminalName($separatedBy->separator),
                $this->nodeName($separatedBy->node)
            );
        }

        $fold = $this->attribute($reflection, Fold::class);
        if ($fold instanceof Fold) {
            return sprintf(
                '%s ((%s) %s)*',
                $this->nodeName($fold->operand),
                $this->terminalNames($fold->operators),
                $this->nodeName($fold->operand)
            );
        }

        $unsupported = $this->unsupportedParserAttribute($reflection);
        if ($unsupported !== null) {
            return sprintf('<%s>', $unsupported);
        }

        if ($reflection->implementsInterface(ParseableNodeInterface::class)) {
            return '<custom>';
        }

        return '<custom>';
    }

    /**
     * @template T of object
     * @param ReflectionClass<object> $reflection
     * @param class-string<T> $attributeClass
     * @return T|null
     */
    private function attribute(ReflectionClass $reflection, string $attributeClass): ?object
    {
        $attributes = $reflection->getAttributes($attributeClass);
        if ($attributes === []) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return list<class-string>
     */
    private function dependencies(ReflectionClass $reflection): array
    {
        $oneOf = $this->attribute($reflection, OneOf::class);
        if ($oneOf instanceof OneOf) {
            return $oneOf->nodes;
        }

        $between = $this->attribute($reflection, Between::class);
        if ($between instanceof Between) {
            return [$between->node];
        }

        $listBetween = $this->attribute($reflection, ListBetween::class);
        if ($listBetween instanceof ListBetween) {
            return [$listBetween->item];
        }

        $optional = $this->attribute($reflection, Optional::class);
        if ($optional instanceof Optional) {
            return [$optional->node];
        }

        $many = $this->attribute($reflection, Many::class);
        if ($many instanceof Many) {
            return [$many->node];
        }

        $separatedBy = $this->attribute($reflection, SeparatedBy::class);
        if ($separatedBy instanceof SeparatedBy) {
            return [$separatedBy->node];
        }

        $fold = $this->attribute($reflection, Fold::class);
        if ($fold instanceof Fold) {
            return [$fold->operand];
        }

        return [];
    }

    private function nodeName(string $nodeClass): string
    {
        return (new ReflectionClass($nodeClass))->getShortName();
    }

    private function terminalName(UnitEnum $terminal): string
    {
        return $terminal->name;
    }

    /**
     * @param UnitEnum|non-empty-list<UnitEnum> $terminals
     */
    private function terminalNames(UnitEnum|array $terminals): string
    {
        if ($terminals instanceof UnitEnum) {
            return $this->terminalName($terminals);
        }

        return implode(' | ', array_map($this->terminalName(...), $terminals));
    }

    /**
     * @param ReflectionClass<object> $reflection
     */
    private function unsupportedParserAttribute(ReflectionClass $reflection): ?string
    {
        foreach ($reflection->getAttributes() as $attribute) {
            $name = $attribute->getName();

            if (!str_starts_with($name, 'Lexicon\\Parser\\Attributes\\')) {
                continue;
            }

            if (in_array($name, [
                Between::class,
                Fold::class,
                ListBetween::class,
                Many::class,
                OneOf::class,
                Optional::class,
                SeparatedBy::class,
                Terminal::class,
            ], true)) {
                continue;
            }

            return $this->shortAttributeName($attribute);
        }

        return null;
    }

    /**
     * @param ReflectionAttribute<object> $attribute
     */
    private function shortAttributeName(ReflectionAttribute $attribute): string
    {
        $parts = explode('\\', $attribute->getName());

        return $parts[array_key_last($parts)];
    }
}
