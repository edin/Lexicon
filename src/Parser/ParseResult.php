<?php

declare(strict_types=1);

namespace Lexicon\Parser;

final readonly class ParseResult
{
    private function __construct(
        public bool $matched,
        public mixed $value = null
    ) {
    }

    public static function match(mixed $value): self
    {
        return new self(true, $value);
    }

    public static function noMatch(): self
    {
        return new self(false);
    }
}
