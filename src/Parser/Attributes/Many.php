<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Many
{
    /**
     * @param class-string<object> $node
     */
    public function __construct(public string $node)
    {
    }
}
