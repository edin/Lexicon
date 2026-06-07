<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Attributes;

use Attribute;
use Lexicon\Lexer\TokenGroup;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final readonly class EndOfFile extends TokenAttribute
{
    public function __construct(?UnitEnum $in = null)
    {
        parent::__construct(TokenGroup::EndOfFile, in: $in);
    }
}
