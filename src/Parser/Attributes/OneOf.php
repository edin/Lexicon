<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use Lexicon\Parser\ParsletFactoryInterface;
use Lexicon\Parser\ParsletInterface;
use Lexicon\Parser\ParsletProviderInterface;
use Lexicon\Parser\Parslets\OneOfParslet;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class OneOf implements ParsletProviderInterface
{
    /**
     * @param non-empty-list<class-string<object>> $nodes
     */
    public function __construct(
        public array $nodes,
        public string $message = 'Expected one of parser alternatives.'
    )
    {
    }

    public function parslet(ParsletFactoryInterface $factory): ParsletInterface
    {
        return new OneOfParslet($this);
    }
}
