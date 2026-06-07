<?php

declare(strict_types=1);

namespace Lexicon\Tests\Fixtures;

use Lexicon\Parser\Parser;

final readonly class JsonParser
{
    public static function parse(Parser $parser): JsonNodeInterface
    {
        $value = self::tryParseValue($parser);
        if ($value !== null) {
            $parser->expect(JsonTokenType::EndOfFile, 'Expected end of JSON document.');

            return $value;
        }

        $parser->expect(JsonTokenType::StringLiteral, 'Expected JSON value.');

        return new JsonStringNode($parser->tokens->current());
    }

    public static function tryParseValue(Parser $parser): ?JsonNodeInterface
    {
        return $parser->oneOf([
            self::tryParseObject(...),
            self::tryParseArray(...),
            self::tryParseString(...),
            self::tryParseNumber(...),
            self::tryParseBoolean(...),
            self::tryParseNull(...),
        ]);
    }

    private static function tryParseObject(Parser $parser): ?JsonObjectNode
    {
        if (!$parser->tokens->check(JsonTokenType::LeftBrace)) {
            return null;
        }

        return new JsonObjectNode($parser->listBetween(
            JsonTokenType::LeftBrace,
            self::tryParseMember(...),
            JsonTokenType::Comma,
            JsonTokenType::RightBrace,
            openMessage: 'Expected {.',
            closeMessage: 'Expected }.'
        ));
    }

    private static function tryParseMember(Parser $parser): ?JsonMemberNode
    {
        $key = self::tryParseString($parser);
        if ($key === null) {
            return null;
        }

        $parser->expect(JsonTokenType::Colon, 'Expected : after object key.');
        $value = self::tryParseValue($parser);

        if ($value === null) {
            $parser->expect(JsonTokenType::StringLiteral, 'Expected JSON value after :.');
            $value = new JsonStringNode($parser->tokens->current());
        }

        return new JsonMemberNode($key, $value);
    }

    private static function tryParseArray(Parser $parser): ?JsonArrayNode
    {
        if (!$parser->tokens->check(JsonTokenType::LeftBracket)) {
            return null;
        }

        return new JsonArrayNode($parser->listBetween(
            JsonTokenType::LeftBracket,
            self::tryParseValue(...),
            JsonTokenType::Comma,
            JsonTokenType::RightBracket,
            openMessage: 'Expected [.',
            closeMessage: 'Expected ].'
        ));
    }

    private static function tryParseString(Parser $parser): ?JsonStringNode
    {
        $token = $parser->tokens->match(JsonTokenType::StringLiteral);
        if ($token === null) {
            return null;
        }

        return new JsonStringNode($token);
    }

    private static function tryParseNumber(Parser $parser): ?JsonNumberNode
    {
        $token = $parser->tokens->match(JsonTokenType::Number);
        if ($token === null) {
            return null;
        }

        return new JsonNumberNode($token);
    }

    private static function tryParseBoolean(Parser $parser): ?JsonBooleanNode
    {
        $token = $parser->tokens->match(JsonTokenType::True)
            ?? $parser->tokens->match(JsonTokenType::False);

        if ($token === null) {
            return null;
        }

        return new JsonBooleanNode($token);
    }

    private static function tryParseNull(Parser $parser): ?JsonNullNode
    {
        $token = $parser->tokens->match(JsonTokenType::Null);
        if ($token === null) {
            return null;
        }

        return new JsonNullNode($token);
    }
}
