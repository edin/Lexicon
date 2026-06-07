<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Lexer\Lexer;
use Lexicon\Parser\Parser;
use Lexicon\Tests\Fixtures\JsonArrayNode;
use Lexicon\Tests\Fixtures\JsonBooleanNode;
use Lexicon\Tests\Fixtures\JsonNodeInterface;
use Lexicon\Tests\Fixtures\JsonNullNode;
use Lexicon\Tests\Fixtures\JsonNumberNode;
use Lexicon\Tests\Fixtures\JsonObjectNode;
use Lexicon\Tests\Fixtures\JsonParser;
use Lexicon\Tests\Fixtures\JsonStringNode;
use Lexicon\Tests\Fixtures\JsonTokenType;
use PHPUnit\Framework\TestCase;

final class JsonParserTest extends TestCase
{
    public function testJsonParserParsesNestedJsonObject(): void
    {
        $node = self::parseJson('{"ok": true, "items": [1, "two", null], "nested": {"n": -12.5e+2}}');

        self::assertInstanceOf(JsonObjectNode::class, $node);
        self::assertCount(3, $node->members);
        self::assertSame('"ok"', $node->members[0]->key->token->value);
        self::assertInstanceOf(JsonBooleanNode::class, $node->members[0]->value);
        self::assertSame('true', $node->members[0]->value->token->value);

        self::assertSame('"items"', $node->members[1]->key->token->value);
        self::assertInstanceOf(JsonArrayNode::class, $node->members[1]->value);
        self::assertCount(3, $node->members[1]->value->items);
        self::assertInstanceOf(JsonNumberNode::class, $node->members[1]->value->items[0]);
        self::assertSame('1', $node->members[1]->value->items[0]->token->value);
        self::assertInstanceOf(JsonStringNode::class, $node->members[1]->value->items[1]);
        self::assertSame('"two"', $node->members[1]->value->items[1]->token->value);
        self::assertInstanceOf(JsonNullNode::class, $node->members[1]->value->items[2]);

        self::assertSame('"nested"', $node->members[2]->key->token->value);
        self::assertInstanceOf(JsonObjectNode::class, $node->members[2]->value);
        self::assertCount(1, $node->members[2]->value->members);
        self::assertInstanceOf(JsonNumberNode::class, $node->members[2]->value->members[0]->value);
        self::assertSame('-12.5e+2', $node->members[2]->value->members[0]->value->token->value);
    }

    public function testJsonParserParsesTopLevelArray(): void
    {
        $node = self::parseJson('[true, false, null]');

        self::assertInstanceOf(JsonArrayNode::class, $node);
        self::assertCount(3, $node->items);
        self::assertInstanceOf(JsonBooleanNode::class, $node->items[0]);
        self::assertInstanceOf(JsonBooleanNode::class, $node->items[1]);
        self::assertInstanceOf(JsonNullNode::class, $node->items[2]);
    }

    public function testJsonParserReportsMissingColonInObjectMember(): void
    {
        $tokens = Lexer::from(JsonTokenType::class)->scan('{"ok" true}');
        $parser = Parser::fromTokens($tokens);

        JsonParser::parse($parser);

        self::assertTrue($parser->diagnostics->hasErrors());
        self::assertSame('Expected : after object key.', $parser->diagnostics->all()[0]->message);
    }

    private static function parseJson(string $source): JsonNodeInterface
    {
        $tokens = Lexer::from(JsonTokenType::class)->scan($source);
        $parser = Parser::fromTokens($tokens);
        $node = JsonParser::parse($parser);

        self::assertFalse($parser->diagnostics->hasErrors());

        return $node;
    }
}
