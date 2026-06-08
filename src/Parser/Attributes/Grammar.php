<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Grammar
{
    /**
     * @param list<class-string<object>> $dependencies
     */
    public function __construct(
        public string $expression,
        public array $dependencies = []
    )
    {
    }
}
