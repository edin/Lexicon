<?php

declare(strict_types=1);

namespace Lexicon\Parser\Debug;

use Lexicon\Parser\Attributes\Between;
use Lexicon\Parser\Attributes\Fold;
use Lexicon\Parser\Attributes\Grammar;
use Lexicon\Parser\Attributes\ListBetween;
use Lexicon\Parser\Attributes\Many;
use Lexicon\Parser\Attributes\OneOf;
use Lexicon\Parser\Attributes\Optional;
use Lexicon\Parser\Attributes\PrefixMany;
use Lexicon\Parser\Attributes\SeparatedBy;
use Lexicon\Parser\Attributes\SeparatedByRequired;
use Lexicon\Parser\Attributes\Sequence;
use Lexicon\Parser\Attributes\Terminal;
use Lexicon\Parser\Part;
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
        $printer->rules[] = sprintf('Start ::= %s', $printer->nodeName($nodeClass));
        $printer->rules[] = '';
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
        $grammar = $this->attribute($reflection, Grammar::class);
        if ($grammar instanceof Grammar) {
            return $grammar->expression;
        }

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
            return sprintf('%s*', $this->manyNodeName($many->node));
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

        $separatedByRequired = $this->attribute($reflection, SeparatedByRequired::class);
        if ($separatedByRequired instanceof SeparatedByRequired) {
            return sprintf(
                '%s (%s %s)*',
                $this->nodeName($separatedByRequired->node),
                $this->terminalName($separatedByRequired->separator),
                $this->nodeName($separatedByRequired->node)
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

        $sequences = $this->attributes($reflection, Sequence::class);
        if ($sequences !== []) {
            $alternatives = [];
            foreach ($sequences as $sequence) {
                $parts = array_map($this->sequencePart(...), $sequence->parts);
                $prefixMany = $this->attribute($reflection, PrefixMany::class);
                if ($prefixMany instanceof PrefixMany) {
                    array_unshift($parts, sprintf('%s*', $this->nodeName($prefixMany->node)));
                }

                $alternatives[] = implode(' ', $parts);
            }

            return implode(' | ', $alternatives);
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
     * @template T of object
     * @param ReflectionClass<object> $reflection
     * @param class-string<T> $attributeClass
     * @return list<T>
     */
    private function attributes(ReflectionClass $reflection, string $attributeClass): array
    {
        return array_map(
            fn (\ReflectionAttribute $attribute): object => $attribute->newInstance(),
            $reflection->getAttributes($attributeClass)
        );
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return list<class-string>
     */
    private function dependencies(ReflectionClass $reflection): array
    {
        $grammar = $this->attribute($reflection, Grammar::class);
        if ($grammar instanceof Grammar) {
            return $grammar->dependencies;
        }

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
            return is_string($many->node) ? [$many->node] : $many->node;
        }

        $separatedBy = $this->attribute($reflection, SeparatedBy::class);
        if ($separatedBy instanceof SeparatedBy) {
            return [$separatedBy->node];
        }

        $separatedByRequired = $this->attribute($reflection, SeparatedByRequired::class);
        if ($separatedByRequired instanceof SeparatedByRequired) {
            return [$separatedByRequired->node];
        }

        $fold = $this->attribute($reflection, Fold::class);
        if ($fold instanceof Fold) {
            return [$fold->operand];
        }

        $sequences = $this->attributes($reflection, Sequence::class);
        if ($sequences !== []) {
            $dependencies = [];
            foreach ($sequences as $sequence) {
                array_push(
                    $dependencies,
                    ...$this->sequenceDependencies($sequence->parts)
                );
            }

            $prefixMany = $this->attribute($reflection, PrefixMany::class);
            if ($prefixMany instanceof PrefixMany) {
                array_unshift($dependencies, $prefixMany->node);
            }

            return array_values(array_unique($dependencies));
        }

        return [];
    }

    private function nodeName(string $nodeClass): string
    {
        return (new ReflectionClass($nodeClass))->getShortName();
    }

    /**
     * @param class-string<object>|non-empty-list<class-string<object>> $node
     */
    private function manyNodeName(string|array $node): string
    {
        if (is_string($node)) {
            return $this->nodeName($node);
        }

        return sprintf('(%s)', implode(' | ', array_map($this->nodeName(...), $node)));
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
     * @param list<mixed> $parts
     * @return list<class-string<object>>
     */
    private function sequenceDependencies(array $parts): array
    {
        $dependencies = [];

        foreach ($parts as $part) {
            if (is_string($part)) {
                $dependencies[] = $part;
                continue;
            }

            if (is_array($part) && ($part[0] ?? null) instanceof Part) {
                array_push($dependencies, ...$this->sequenceDependencies(array_slice($part, 1)));
            }
        }

        return $dependencies;
    }

    /**
     * @param UnitEnum|class-string<object>|non-empty-list<UnitEnum>|array{0: Part, ...} $part
     */
    private function sequencePart(UnitEnum|string|array $part): string
    {
        if ($part instanceof UnitEnum) {
            return $this->terminalName($part);
        }

        if (is_string($part)) {
            return $this->nodeName($part);
        }

        $first = $part[0];
        if ($first instanceof Part) {
            return $this->partDescriptor($first, array_slice($part, 1));
        }

        return sprintf('(%s)', $this->terminalNames($part));
    }

    /**
     * @param list<mixed> $arguments
     */
    private function partDescriptor(Part $part, array $arguments): string
    {
        return match ($part) {
            Part::Optional => sprintf('%s?', $this->sequencePart($arguments[0])),
            Part::Many => sprintf('%s*', $this->sequencePart($arguments[0])),
            Part::OneOrMore => sprintf('%s+', $this->sequencePart($arguments[0])),
            Part::SeparatedBy => sprintf(
                '(%s (%s %s)*)?',
                $this->sequencePart($arguments[0]),
                $this->terminalName($arguments[1]),
                $this->sequencePart($arguments[0])
            ),
            Part::SeparatedByRequired => sprintf(
                '%s (%s %s)*',
                $this->sequencePart($arguments[0]),
                $this->terminalName($arguments[1]),
                $this->sequencePart($arguments[0])
            ),
            Part::ListBetween => sprintf(
                '%s (%s (%s %s)*)? %s',
                $this->terminalName($arguments[2]),
                $this->sequencePart($arguments[0]),
                $this->terminalName($arguments[1]),
                $this->sequencePart($arguments[0]),
                $this->terminalName($arguments[3])
            ),
            Part::ManyUntil => sprintf(
                '%s* /* until %s */',
                $this->sequencePart($arguments[0]),
                $this->terminalNames($arguments[1])
            ),
            Part::ManyUntilRequired => sprintf(
                '%s+ /* until %s */',
                $this->sequencePart($arguments[0]),
                $this->terminalNames($arguments[1])
            ),
            Part::OptionalSequence => sprintf(
                '(%s)?',
                implode(' ', array_map($this->sequencePart(...), $arguments))
            ),
        };
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
                Grammar::class,
                ListBetween::class,
                Many::class,
                OneOf::class,
                Optional::class,
                PrefixMany::class,
                SeparatedBy::class,
                SeparatedByRequired::class,
                Sequence::class,
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
