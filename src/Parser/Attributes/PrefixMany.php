<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParserAttributeInterface;
use LogicException;
use ReflectionClass;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class PrefixMany implements ParserAttributeInterface
{
    /**
     * @param class-string<object> $node
     */
    public function __construct(public string $node)
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): ?object
    {
        $sequenceAttributes = $nodeClass->getAttributes(Sequence::class);
        if ($sequenceAttributes === []) {
            throw new LogicException(sprintf(
                '%s requires %s on %s.',
                self::class,
                Sequence::class,
                $nodeClass->getName()
            ));
        }

        $position = $parser->tokens->save();
        $prefixes = [];

        while (!$parser->tokens->isAtEnd()) {
            $prefixPosition = $parser->tokens->save();
            $prefix = $parser->tryParse($this->node);
            $nextPosition = $parser->tokens->save();

            if ($prefix === null || $nextPosition === $prefixPosition) {
                $parser->tokens->restore($prefixPosition);
                break;
            }

            $prefixes[] = $prefix;
        }

        $node = $parser->parseSequenceAlternatives(
            $nodeClass,
            $sequenceAttributes,
            $report,
            [$prefixes]
        );

        if ($node === null) {
            $parser->tokens->restore($position);
        }

        return $node;
    }
}
