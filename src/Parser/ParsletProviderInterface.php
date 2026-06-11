<?php

declare(strict_types=1);

namespace Lexicon\Parser;

interface ParsletProviderInterface
{
    public function parslet(ParsletFactoryInterface $factory): ParsletInterface;
}
