<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Lexer\Lexer;
use Lexicon\Parser\Debug\AstPrinter;
use Lexicon\Parser\Parser;
use Lexicon\Tests\Fixtures\AddExpressionNode;
use Lexicon\Tests\Fixtures\ExpressionTokenType;
use Lexicon\Tests\Fixtures\JsonParser;
use Lexicon\Tests\Fixtures\JsonTokenType;
use PHPUnit\Framework\TestCase;

final class AstPrinterTest extends TestCase
{
    public function testAstPrinterFormatsExpressionShape(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 + 2 + 3');
        $node = Parser::fromTokens($tokens)->parse(AddExpressionNode::class);

        $tree = AstPrinter::format($node);

        self::assertSame(str_replace("\n", PHP_EOL, <<<'TXT'
AddExpressionNode
  operator: Plus "+"
  left: AddExpressionNode
    operator: Plus "+"
    left: IntegerNode "1"
    right: IntegerNode "2"
  right: IntegerNode "3"
TXT), $tree);
    }

    public function testAstPrinterFormatsJsonShapeWithoutSourceDetails(): void
    {
        $tokens = Lexer::from(JsonTokenType::class)->scan('{"items": [1, "two", null]}');
        $node = JsonParser::parse(Parser::fromTokens($tokens));

        $tree = AstPrinter::format($node);

        self::assertStringContainsString('JsonObjectNode', $tree);
        self::assertStringContainsString('members: list', $tree);
        self::assertStringContainsString('[0]: JsonMemberNode', $tree);
        self::assertStringContainsString('key: JsonStringNode "\"items\""', $tree);
        self::assertStringContainsString('value: JsonArrayNode', $tree);
        self::assertStringContainsString('[0]: JsonNumberNode "1"', $tree);
        self::assertStringContainsString('[1]: JsonStringNode "\"two\""', $tree);
        self::assertStringContainsString('[2]: JsonNullNode "null"', $tree);
        self::assertStringNotContainsString('SourceFile', $tree);
        self::assertStringNotContainsString('location', $tree);
        self::assertStringNotContainsString('leadingTrivia', $tree);
    }

    public function testAstPrinterShrinksLongValues(): void
    {
        $tokens = Lexer::from(JsonTokenType::class)->scan('"very very long string value"');
        $node = JsonParser::parse(Parser::fromTokens($tokens));

        $tree = AstPrinter::format($node, maxValueLength: 12);

        self::assertSame('JsonStringNode "\"very ve..."', $tree);
    }
}
