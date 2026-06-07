<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Attributes;

use Attribute;
use Lexicon\Lexer\TokenGroup;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final readonly class Symbol extends TokenAttribute
{
    public function __construct(
        string $text,
        ?UnitEnum $in = null,
        ?UnitEnum $enter = null,
        ?UnitEnum $push = null,
        bool $pop = false,
    )
    {
        parent::__construct(TokenGroup::Symbol, $text, in: $in, enter: $enter, push: $push, pop: $pop);
    }
}
