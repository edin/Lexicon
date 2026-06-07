<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

final readonly class JsonMemberNode
{
    public function __construct(
        public JsonStringNode $key,
        public JsonNodeInterface $value
    )
    {
    }
}
