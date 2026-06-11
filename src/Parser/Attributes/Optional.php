<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use Lexicon\Parser\ParsletFactoryInterface;
use Lexicon\Parser\ParsletInterface;
use Lexicon\Parser\ParsletProviderInterface;
use Lexicon\Parser\Parslets\OptionalParslet;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Optional implements ParsletProviderInterface
{
    /**
     * @param class-string<object> $node
     */
    public function __construct(public string $node)
    {
    }

    public function parslet(ParsletFactoryInterface $factory): ParsletInterface
    {
        return new OptionalParslet($this);
    }
}
