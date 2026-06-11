<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use Lexicon\Parser\ParsletFactoryInterface;
use Lexicon\Parser\ParsletInterface;
use Lexicon\Parser\ParsletProviderInterface;
use LogicException;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Parse implements ParsletProviderInterface
{
    /**
     * @param class-string<ParsletInterface>|array{0: \UnitEnum, ...} $parslet
     * @param array<array-key, mixed> $arguments
     */
    public function __construct(
        public string|array $parslet,
        public array $arguments = []
    ) {
    }

    public function parslet(ParsletFactoryInterface $factory): ParsletInterface
    {
        return $factory->forDefinition($this->parslet, $this->arguments)
            ?? throw new LogicException('Unable to resolve parse attribute parslet.');
    }
}
