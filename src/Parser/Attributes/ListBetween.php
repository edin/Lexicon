<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use Lexicon\Parser\ParsletFactoryInterface;
use Lexicon\Parser\ParsletInterface;
use Lexicon\Parser\ParsletProviderInterface;
use Lexicon\Parser\Parslets\ListBetweenParslet;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ListBetween implements ParsletProviderInterface
{
    /**
     * @param class-string<object> $item
     */
    public function __construct(
        public UnitEnum $open,
        public string $item,
        public UnitEnum $separator,
        public UnitEnum $close,
        public bool $allowTrailingSeparator = false,
        public string $openMessage = 'Expected opening token.',
        public string $closeMessage = 'Expected closing token.'
    )
    {
    }

    public function parslet(ParsletFactoryInterface $factory): ParsletInterface
    {
        return new ListBetweenParslet($this);
    }
}
