<?php

declare(strict_types=1);

namespace Lexicon\Parser;

interface ParseableNodeInterface
{
    public static function parse(Parser $parser): static;
}
