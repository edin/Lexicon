<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use Lexicon\Parser\ParsletFactoryInterface;
use Lexicon\Parser\ParsletInterface;
use Lexicon\Parser\ParsletProviderInterface;
use Lexicon\Parser\Parslets\SeparatedByParslet;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class SeparatedBy implements ParsletProviderInterface
{
    /**
     * @param class-string<object> $node
     */
    public function __construct(
        public string $node,
        public UnitEnum $separator,
        public bool $allowTrailingSeparator = false
    )
    {
    }

    public function parslet(ParsletFactoryInterface $factory): ParsletInterface
    {
        return new SeparatedByParslet($this);
    }
}
