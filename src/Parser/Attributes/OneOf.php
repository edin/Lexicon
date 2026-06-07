<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class OneOf
{
    /**
     * @param non-empty-list<class-string<object>> $nodes
     */
    public function __construct(
        public array $nodes,
        public string $message = 'Expected one of parser alternatives.'
    )
    {
    }
}
