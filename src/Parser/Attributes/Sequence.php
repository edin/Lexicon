<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Sequence
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
}
