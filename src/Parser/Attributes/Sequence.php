<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use Lexicon\Parser\NodeParsletProviderInterface;
use Lexicon\Parser\ParsletFactoryInterface;
use Lexicon\Parser\ParsletInterface;
use Lexicon\Parser\Parslets\SequenceAlternativesParslet;
use ReflectionClass;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Sequence implements NodeParsletProviderInterface
{
    /**
     * @param non-empty-list<mixed> $parts
     */
    public function __construct(
        public array $parts,
        public ?string $factory = null
    )
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parsletForNode(ReflectionClass $nodeClass, ParsletFactoryInterface $factory): ?ParsletInterface
    {
        $sequences = $nodeClass->getAttributes(self::class);

        return $sequences === []
            ? null
            : new SequenceAlternativesParslet($sequences);
    }
}
