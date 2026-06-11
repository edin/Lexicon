<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use Lexicon\Parser\ParsletFactoryInterface;
use Lexicon\Parser\ParsletInterface;
use Lexicon\Parser\ParsletProviderInterface;
use Lexicon\Parser\Parslets\TerminalParslet;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Terminal implements ParsletProviderInterface
{
    public function __construct(
        public UnitEnum $type,
        public string $message = 'Expected terminal.'
    )
    {
    }

    public function parslet(ParsletFactoryInterface $factory): ParsletInterface
    {
        return new TerminalParslet($this);
    }
}
