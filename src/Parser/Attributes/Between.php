<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Between
{
    /**
     * @param class-string<object> $node
     */
    public function __construct(
        public UnitEnum $open,
        public string $node,
        public UnitEnum $close,
        public string $openMessage = 'Expected opening token.',
        public string $closeMessage = 'Expected closing token.'
    )
    {
    }
}
