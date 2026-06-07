<?php

declare(strict_types=1);

namespace Lexicon\Parser\Debug;

use Lexicon\Lexer\Token;
use ReflectionClass;

final class AstPrinter
{
    public static function format(object $node, int $maxValueLength = 60): string
    {
        return implode(PHP_EOL, self::formatValue($node, '', null, $maxValueLength));
    }

    /**
     * @return list<string>
     */
    private static function formatValue(mixed $value, string $indent, ?string $name, int $maxValueLength): array
    {
        if ($value instanceof Token) {
            return [$indent . self::prefix($name) . self::formatToken($value, $maxValueLength)];
        }

        if (is_object($value)) {
            return self::formatObject($value, $indent, $name, $maxValueLength);
        }

        if (is_array($value)) {
            return self::formatArray($value, $indent, $name, $maxValueLength);
        }

        return [$indent . self::prefix($name) . self::formatScalar($value, $maxValueLength)];
    }

    /**
     * @return list<string>
     */
    private static function formatObject(object $node, string $indent, ?string $name, int $maxValueLength): array
    {
        $reflection = new ReflectionClass($node);
        $properties = $reflection->getProperties();
        $summary = self::nodeSummary($node, $maxValueLength);
        $lines = [$indent . self::prefix($name) . $summary];
        $childIndent = $indent . '  ';

        foreach ($properties as $property) {
            if (!$property->isPublic() || $property->isStatic()) {
                continue;
            }

            $propertyValue = $property->getValue($node);

            if ($propertyValue instanceof Token && $property->getName() === 'token') {
                continue;
            }

            $lines = [
                ...$lines,
                ...self::formatValue($propertyValue, $childIndent, $property->getName(), $maxValueLength),
            ];
        }

        return $lines;
    }

    /**
     * @param array<array-key, mixed> $values
     * @return list<string>
     */
    private static function formatArray(array $values, string $indent, ?string $name, int $maxValueLength): array
    {
        $lines = [$indent . self::prefix($name) . 'list'];
        $childIndent = $indent . '  ';

        foreach ($values as $index => $value) {
            $lines = [
                ...$lines,
                ...self::formatValue($value, $childIndent, sprintf('[%s]', (string) $index), $maxValueLength),
            ];
        }

        return $lines;
    }

    private static function nodeSummary(object $node, int $maxValueLength): string
    {
        $reflection = new ReflectionClass($node);
        $name = $reflection->getShortName();

        if ($reflection->hasProperty('token')) {
            $property = $reflection->getProperty('token');
            if ($property->isPublic() && !$property->isStatic()) {
                $token = $property->getValue($node);
                if ($token instanceof Token) {
                    return sprintf('%s %s', $name, self::quoted($token->value, $maxValueLength));
                }
            }
        }

        return $name;
    }

    private static function formatToken(Token $token, int $maxValueLength): string
    {
        return sprintf('%s %s', $token->type->name, self::quoted($token->value, $maxValueLength));
    }

    private static function formatScalar(mixed $value, int $maxValueLength): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return self::quoted($value, $maxValueLength);
        }

        return get_debug_type($value);
    }

    private static function prefix(?string $name): string
    {
        return $name === null ? '' : $name . ': ';
    }

    private static function quoted(string $value, int $maxValueLength): string
    {
        return '"' . self::shrink(self::escape($value), $maxValueLength) . '"';
    }

    private static function escape(string $value): string
    {
        return strtr($value, [
            '\\' => '\\\\',
            '"' => '\"',
            "\r" => '\r',
            "\n" => '\n',
            "\t" => '\t',
        ]);
    }

    private static function shrink(string $value, int $maxLength): string
    {
        if ($maxLength <= 0 || strlen($value) <= $maxLength) {
            return $value;
        }

        if ($maxLength <= 3) {
            return substr($value, 0, $maxLength);
        }

        return substr($value, 0, $maxLength - 3) . '...';
    }
}
