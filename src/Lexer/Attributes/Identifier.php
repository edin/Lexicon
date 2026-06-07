<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Attributes;

use Attribute;
use Lexicon\Lexer\Matchers\IdentifierTokenMatcher;
use Lexicon\Lexer\Matchers\TokenMatcherInterface;
use Lexicon\Lexer\TokenGroup;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final readonly class Identifier extends TokenAttribute
{
    /**
     * @param class-string<TokenMatcherInterface> $matcherClass
     */
    public function __construct(
        string $matcherClass = IdentifierTokenMatcher::class,
        ?UnitEnum $in = null,
        ?UnitEnum $enter = null,
        ?UnitEnum $push = null,
        bool $pop = false,
    )
    {
        parent::__construct(TokenGroup::Identifier, matcherClass: $matcherClass, in: $in, enter: $enter, push: $push, pop: $pop);
    }
}
