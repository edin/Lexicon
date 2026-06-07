<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

final readonly class JsonArrayNode implements JsonNodeInterface
{
    /**
     * @param list<JsonNodeInterface> $items
     */
    public function __construct(public array $items)
    {
    }
}
