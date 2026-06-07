<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ListBetween
{
    /**
     * @param class-string<object> $item
     */
    public function __construct(
        public UnitEnum $open,
        public string $item,
        public UnitEnum $separator,
        public UnitEnum $close,
        public bool $allowTrailingSeparator = false,
        public string $openMessage = 'Expected opening token.',
        public string $closeMessage = 'Expected closing token.'
    )
    {
    }
}
