<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Parser\Debug\GrammarPrinter;
use Lexicon\Tests\Fixtures\AddExpressionNode;
use Lexicon\Tests\Fixtures\AttributeExpressionNodeInterface;
use Lexicon\Tests\Fixtures\AttributeIntegerListNode;
use Lexicon\Tests\Fixtures\AttributeManyIntegerNode;
use Lexicon\Tests\Fixtures\AttributeOptionalIntegerNode;
use Lexicon\Tests\Fixtures\AttributeSeparatedIntegerNode;
use PHPUnit\Framework\TestCase;

final class GrammarPrinterTest extends TestCase
{
    public function testGrammarPrinterFormatsFoldRecipe(): void
    {
        $grammar = GrammarPrinter::format(AddExpressionNode::class);

        self::assertSame(implode(PHP_EOL, [
            'AddExpressionNode ::= IntegerNode ((Plus) IntegerNode)*',
            'IntegerNode ::= <custom>',
        ]), $grammar);
    }

    public function testGrammarPrinterFormatsOneOfBetweenAndTerminalRecipes(): void
    {
        $grammar = GrammarPrinter::format(AttributeExpressionNodeInterface::class);

        self::assertSame(implode(PHP_EOL, [
            'AttributeExpressionNodeInterface ::= AttributeGroupedIntegerNode | AttributeIntegerNode',
            'AttributeGroupedIntegerNode ::= OpenParen AttributeIntegerNode CloseParen',
            'AttributeIntegerNode ::= Integer',
        ]), $grammar);
    }

    public function testGrammarPrinterFormatsListBetweenRecipe(): void
    {
        $grammar = GrammarPrinter::format(AttributeIntegerListNode::class);

        self::assertSame(implode(PHP_EOL, [
            'AttributeIntegerListNode ::= OpenParen (AttributeIntegerNode (Comma AttributeIntegerNode)*)? CloseParen',
            'AttributeIntegerNode ::= Integer',
        ]), $grammar);
    }

    public function testGrammarPrinterFormatsOptionalManyAndSeparatedByRecipes(): void
    {
        self::assertSame(implode(PHP_EOL, [
            'AttributeOptionalIntegerNode ::= AttributeIntegerNode?',
            'AttributeIntegerNode ::= Integer',
        ]), GrammarPrinter::format(AttributeOptionalIntegerNode::class));

        self::assertSame(implode(PHP_EOL, [
            'AttributeManyIntegerNode ::= AttributeIntegerNode*',
            'AttributeIntegerNode ::= Integer',
        ]), GrammarPrinter::format(AttributeManyIntegerNode::class));

        self::assertSame(implode(PHP_EOL, [
            'AttributeSeparatedIntegerNode ::= (AttributeIntegerNode (Comma AttributeIntegerNode)*)?',
            'AttributeIntegerNode ::= Integer',
        ]), GrammarPrinter::format(AttributeSeparatedIntegerNode::class));
    }
}
