<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use Lexicon\Parser\ParsletFactoryInterface;
use Lexicon\Parser\ParsletInterface;
use Lexicon\Parser\ParsletProviderInterface;
use Lexicon\Parser\Parslets\BetweenParslet;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Between implements ParsletProviderInterface
{
    /**
     * @param class-string<object> $node
     */
    public function __construct(
        public UnitEnum $open,
        public string $node,
        public UnitEnum $close,
        public string $openMessage = 'Expected opening token.',
        public string $closeMessage = 'Expected closing token.'
    )
    {
    }

    public function parslet(ParsletFactoryInterface $factory): ParsletInterface
    {
        return new BetweenParslet($this);
    }
}
