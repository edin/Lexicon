<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class SeparatedBy
{
    /**
     * @param class-string<object> $node
     */
    public function __construct(
        public string $node,
        public UnitEnum $separator,
        public bool $allowTrailingSeparator = false
    )
    {
    }
}
