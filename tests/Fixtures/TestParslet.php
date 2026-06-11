<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use InvalidArgumentException;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletDispatchInterface;
use ReflectionClass;

enum TestParslet implements ParsletDispatchInterface
{
    case Integer;

    /**
     * @param ReflectionClass<object> $nodeClass
     * @param list<mixed> $arguments
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report, array $arguments): ?object
    {
        return match ($this) {
            self::Integer => $this->parseInteger($parser, $nodeClass, $report, $arguments),
        };
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     * @param list<mixed> $arguments
     */
    private function parseInteger(Parser $parser, ReflectionClass $nodeClass, bool $report, array $arguments): ?object
    {
        $message = $arguments[0] ?? 'Expected dispatched integer.';
        if (!is_string($message)) {
            throw new InvalidArgumentException('Integer parslet message must be a string.');
        }

        $token = $report
            ? $parser->expect(ExpressionTokenType::Integer, $message)
            : $parser->tokens->match(ExpressionTokenType::Integer);

        return $token === null ? null : $nodeClass->newInstance($token);
    }
}
