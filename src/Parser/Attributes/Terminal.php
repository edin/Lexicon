<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Terminal
{
    public function __construct(
        public UnitEnum $type,
        public string $message = 'Expected terminal.'
    )
    {
    }
}
