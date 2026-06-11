<?php

declare(strict_types=1);

namespace Lexicon\Parser\Attributes;

use Attribute;
use Lexicon\Parser\Associativity;
use Lexicon\Parser\ParsletFactoryInterface;
use Lexicon\Parser\ParsletInterface;
use Lexicon\Parser\ParsletProviderInterface;
use Lexicon\Parser\Parslets\FoldParslet;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Fold implements ParsletProviderInterface
{
    /**
     * @param UnitEnum|non-empty-list<UnitEnum> $operators
     * @param class-string<object> $operand
     */
    public function __construct(
        public UnitEnum|array $operators,
        public string $operand,
        public Associativity $associativity = Associativity::Left
    )
    {
    }

    public function parslet(ParsletFactoryInterface $factory): ParsletInterface
    {
        return new FoldParslet($this);
    }
}
