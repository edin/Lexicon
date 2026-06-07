<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Attributes;

use InvalidArgumentException;
use Lexicon\Lexer\Matchers\TokenMatcherInterface;
use Lexicon\Lexer\TokenGroup;
use UnitEnum;

abstract readonly class TokenAttribute
{
    /**
     * @param string|null $matcherClass
     */
    public function __construct(
        public TokenGroup $group,
        public ?string $text = null,
        public ?string $matcherClass = null,
        public ?UnitEnum $in = null,
        public ?UnitEnum $enter = null,
        public ?UnitEnum $push = null,
        public bool $pop = false,
    ) {
        if ($matcherClass !== null && !is_subclass_of($matcherClass, TokenMatcherInterface::class)) {
            throw new InvalidArgumentException(
                sprintf("Matcher class '%s' must implement %s.", $matcherClass, TokenMatcherInterface::class)
            );
        }

        $transitionCount = ($enter === null ? 0 : 1) + ($push === null ? 0 : 1) + ($pop ? 1 : 0);
        if ($transitionCount > 1) {
            throw new InvalidArgumentException('Token attributes can define only one mode transition: enter, push, or pop.');
        }
    }
}
