<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class IntegerParslet implements ParsletInterface
{
    public function __construct(private string $message = 'Expected custom parsed integer.')
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): ?object
    {
        $token = $report
            ? $parser->expect(ExpressionTokenType::Integer, $this->message)
            : $parser->tokens->match(ExpressionTokenType::Integer);

        return $token === null ? null : $nodeClass->newInstance($token);
    }
}
