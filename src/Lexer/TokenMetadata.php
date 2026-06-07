<?php

declare(strict_types=1);

namespace Lexicon\Lexer;

use UnitEnum;
use Lexicon\Lexer\Matchers\ITokenMatcher;

final readonly class TokenMetadata
{
    /**
     * @param class-string<ITokenMatcher>|null $matcherClass
     */
    public function __construct(
        public UnitEnum $type,
        public ?string $text,
        public TokenGroup $group,
        public ?string $matcherClass,
        public ?UnitEnum $in,
        public ?UnitEnum $enter,
        public ?UnitEnum $push,
        public bool $pop,
    ) {
    }
}
