<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Debug;

use Lexicon\Lexer\Token;

final class TokenTable
{
    /**
     * @param list<Token> $tokens
     */
    public static function format(
        array $tokens,
        bool $includeTrivia = true,
        int $maxValueLength = 60,
        bool $color = false,
    ): string
    {
        $rows = [[
            'Kind',
            'Group',
            'Mode',
            'Value',
            'Line',
            'Column',
            'Span',
        ]];

        foreach ($tokens as $token) {
            if ($includeTrivia) {
                foreach ($token->leadingTrivia as $trivia) {
                    $rows[] = self::row($trivia, $maxValueLength, $color);
                }
            }

            $rows[] = self::row($token, $maxValueLength, $color);
        }

        return self::render($rows);
    }

    /**
     * @return list<string>
     */
    private static function row(Token $token, int $maxValueLength, bool $color): array
    {
        $span = $token->span();
        $kind = $token->type->name;
        $group = $token->group->name;

        return [
            self::colorize($kind, $token, $color),
            self::colorize($group, $token, $color),
            $token->mode === null ? '<none>' : $token->mode->name,
            self::colorize(self::shrink(self::escape($token->value), $maxValueLength), $token, $color),
            (string) $token->location->line,
            (string) $token->location->column,
            sprintf('%d..%d', $span->start, $span->end()),
        ];
    }

    /**
     * @param list<list<string>> $rows
     */
    private static function render(array $rows): string
    {
        $widths = [];

        foreach ($rows as $row) {
            foreach ($row as $index => $cell) {
                $widths[$index] = max($widths[$index] ?? 0, strlen(self::withoutAnsi($cell)));
            }
        }

        return implode(PHP_EOL, array_map(
            fn (array $row): string => implode('  ', array_map(
                fn (string $cell, int $index): string => $cell . str_repeat(' ', $widths[$index] - strlen(self::withoutAnsi($cell))),
                $row,
                array_keys($row)
            )),
            $rows
        ));
    }

    private static function escape(string $value): string
    {
        if ($value === '') {
            return '<empty>';
        }

        return strtr($value, [
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

    private static function colorize(string $value, Token $token, bool $color): string
    {
        if (!$color || $token->group->name !== 'Keyword') {
            return $value;
        }

        return "\033[36m" . $value . "\033[0m";
    }

    private static function withoutAnsi(string $value): string
    {
        return preg_replace('/\033\[[0-9;]*m/', '', $value) ?? $value;
    }
}
