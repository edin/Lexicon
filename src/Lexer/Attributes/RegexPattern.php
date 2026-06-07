<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Attributes;

use Attribute;
use Lexicon\Lexer\Matchers\RegexTokenMatcher;
use Lexicon\Lexer\TokenGroup;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final readonly class RegexPattern extends TokenAttribute
{
    public function __construct(
        string $pattern,
        TokenGroup $group = TokenGroup::Literal,
        ?UnitEnum $in = null,
        ?UnitEnum $enter = null,
        ?UnitEnum $push = null,
        bool $pop = false,
    )
    {
        parent::__construct($group, $pattern, RegexTokenMatcher::class, $in, $enter, $push, $pop);
    }
}
