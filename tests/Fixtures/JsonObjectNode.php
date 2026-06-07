<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

final readonly class JsonObjectNode implements JsonNodeInterface
{
    /**
     * @param list<JsonMemberNode> $members
     */
    public function __construct(public array $members)
    {
    }
}
