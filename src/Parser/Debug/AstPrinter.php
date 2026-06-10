<?php

declare(strict_types=1);

namespace Lexicon\Parser\Debug;

use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenGroup;
use ReflectionClass;

final class AstPrinter
{
    public static function format(object $node, int $maxValueLength = 60, bool $color = false): string
    {
        return implode(PHP_EOL, self::formatValue($node, '', null, $maxValueLength, $color));
    }

    /**
     * @return list<string>
     */
    private static function formatValue(
        mixed $value,
        string $indent,
        ?string $name,
        int $maxValueLength,
        bool $color,
    ): array
    {
        if ($value instanceof Token) {
            return [$indent . self::prefix($name, $color) . self::formatToken($value, $maxValueLength, $color)];
        }

        if (is_object($value)) {
            return self::formatObject($value, $indent, $name, $maxValueLength, $color);
        }

        if (is_array($value)) {
            return self::formatArray($value, $indent, $name, $maxValueLength, $color);
        }

        return [$indent . self::prefix($name, $color) . self::formatScalar($value, $maxValueLength, $color)];
    }

    /**
     * @return list<string>
     */
    private static function formatObject(
        object $node,
        string $indent,
        ?string $name,
        int $maxValueLength,
        bool $color,
    ): array
    {
        $reflection = new ReflectionClass($node);
        $properties = $reflection->getProperties();
        $summary = self::nodeSummary($node, $maxValueLength, $color);
        $lines = [$indent . self::prefix($name, $color) . $summary];
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
                ...self::formatValue($propertyValue, $childIndent, $property->getName(), $maxValueLength, $color),
            ];
        }

        return $lines;
    }

    /**
     * @param array<array-key, mixed> $values
     * @return list<string>
     */
    private static function formatArray(
        array $values,
        string $indent,
        ?string $name,
        int $maxValueLength,
        bool $color,
    ): array
    {
        $lines = [$indent . self::prefix($name, $color) . self::structural('list', $color)];
        $childIndent = $indent . '  ';

        foreach ($values as $index => $value) {
            $lines = [
                ...$lines,
                ...self::formatValue(
                    $value,
                    $childIndent,
                    self::indexLabel($index),
                    $maxValueLength,
                    $color
                ),
            ];
        }

        return $lines;
    }

    private static function nodeSummary(object $node, int $maxValueLength, bool $color): string
    {
        $reflection = new ReflectionClass($node);
        $name = self::colorizeNode($reflection->getShortName(), $color);

        if ($reflection->hasProperty('token')) {
            $property = $reflection->getProperty('token');
            if ($property->isPublic() && !$property->isStatic()) {
                $token = $property->getValue($node);
                if ($token instanceof Token) {
                    return sprintf('%s %s', $name, self::formatTokenValue($token, $maxValueLength, $color));
                }
            }
        }

        return $name;
    }

    private static function colorizeNode(string $value, bool $color): string
    {
        if (!$color) {
            return $value;
        }

        return self::paint($value, '1;36', $color);
    }

    private static function formatToken(Token $token, int $maxValueLength, bool $color): string
    {
        return sprintf(
            '%s %s',
            self::colorizeTokenType($token, $color),
            self::formatTokenValue($token, $maxValueLength, $color)
        );
    }

    private static function formatScalar(mixed $value, int $maxValueLength, bool $color): string
    {
        if (is_bool($value)) {
            return self::paint($value ? 'true' : 'false', '33', $color);
        }

        if ($value === null) {
            return self::structural('null', $color);
        }

        if (is_int($value) || is_float($value)) {
            return self::paint((string) $value, '33', $color);
        }

        if (is_string($value)) {
            return self::paint(self::quoted($value, $maxValueLength), '35', $color);
        }

        return self::structural(get_debug_type($value), $color);
    }

    private static function prefix(?string $name, bool $color): string
    {
        return $name === null ? '' : self::structural($name . ': ', $color);
    }

    private static function indexLabel(int|string $index): string
    {
        return sprintf('[%s]', (string) $index);
    }

    private static function colorizeTokenType(Token $token, bool $color): string
    {
        return self::paint($token->type->name, self::tokenStyle($token), $color);
    }

    private static function formatTokenValue(Token $token, int $maxValueLength, bool $color): string
    {
        return self::paint(self::quoted($token->value, $maxValueLength), self::tokenStyle($token), $color);
    }

    private static function tokenStyle(Token $token): string
    {
        return match ($token->group) {
            TokenGroup::Identifier => '32',
            TokenGroup::Literal => '35',
            TokenGroup::Keyword => '34',
            TokenGroup::Symbol => '33',
            TokenGroup::EndOfFile, TokenGroup::Trivia, TokenGroup::Unknown => '2',
        };
    }

    private static function structural(string $value, bool $color): string
    {
        return self::paint($value, '2', $color);
    }

    private static function paint(string $value, string $style, bool $color): string
    {
        if (!$color) {
            return $value;
        }

        return "\033[" . $style . 'm' . $value . "\033[0m";
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
