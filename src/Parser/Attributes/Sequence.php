<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Sequence
{
    /**
     * @param non-empty-list<UnitEnum|class-string<object>|non-empty-list<UnitEnum>> $parts
     */
    public function __construct(public array $parts)
    {
    }
}
