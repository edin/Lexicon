<?php

declare(strict_types=1);

namespace Lexicon\Lexer;

final readonly class Span
{
    public function __construct(
        public int $start,
        public int $length,
    ) {
    }

    public function end(): int
    {
        return $this->start + $this->length;
    }
}
