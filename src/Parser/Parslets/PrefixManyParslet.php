<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\Attributes\PrefixMany;
use Lexicon\Parser\Attributes\Sequence;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use LogicException;
use ReflectionClass;

final readonly class PrefixManyParslet implements ParsletInterface
{
    public function __construct(private PrefixMany $prefixMany)
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
                PrefixMany::class,
                Sequence::class,
                $nodeClass->getName()
            ));
        }

        $position = $parser->tokens->save();
        $prefixes = [];

        while (!$parser->tokens->isAtEnd()) {
            $prefixPosition = $parser->tokens->save();
            $prefix = $parser->tryParse($this->prefixMany->node);
            $nextPosition = $parser->tokens->save();

            if ($prefix === null || $nextPosition === $prefixPosition) {
                $parser->tokens->restore($prefixPosition);
                break;
            }

            $prefixes[] = $prefix;
        }

        $node = $parser->parseSequenceAlternatives($nodeClass, $sequenceAttributes, $report, [$prefixes]);

        if ($node === null) {
            $parser->tokens->restore($position);
        }

        return $node;
    }
}
