<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use Lexicon\Parser\Associativity;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Fold
{
    /**
     * @param UnitEnum|non-empty-list<UnitEnum> $operators
     * @param class-string<object> $operand
     */
    public function __construct(
        public UnitEnum|array $operators,
        public string $operand,
        public Associativity $associativity = Associativity::Left
    )
    {
    }
}
