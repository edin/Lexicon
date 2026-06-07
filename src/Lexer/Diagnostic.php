<?php

declare(strict_types=1);

namespace Lexicon\Lexer;

final readonly class Diagnostic
{
    public function __construct(
        public Location $location,
        public string $message,
    ) {
    }
}
