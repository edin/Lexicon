<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Attributes;

use Attribute;
use Lexicon\Lexer\TokenGroup;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final readonly class Fixed extends TokenAttribute
{
    public function __construct(
        string $text,
        TokenGroup $group = TokenGroup::Literal,
        ?UnitEnum $in = null,
        ?UnitEnum $enter = null,
        ?UnitEnum $push = null,
        bool $pop = false,
    ) {
        parent::__construct($group, $text, in: $in, enter: $enter, push: $push, pop: $pop);
    }
}
