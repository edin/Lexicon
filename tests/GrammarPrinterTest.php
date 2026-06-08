<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Parser\Debug\GrammarPrinter;
use Lexicon\Tests\Fixtures\AddExpressionNode;
use Lexicon\Tests\Fixtures\AttributeExpressionNodeInterface;
use Lexicon\Tests\Fixtures\AttributeGrammarNode;
use Lexicon\Tests\Fixtures\AttributeIntegerListNode;
use Lexicon\Tests\Fixtures\AttributeManyIntegerNode;
use Lexicon\Tests\Fixtures\AttributeOptionalIntegerNode;
use Lexicon\Tests\Fixtures\AttributeSeparatedIntegerNode;
use Lexicon\Tests\Fixtures\AttributeGroupedSequenceNode;
use Lexicon\Tests\Fixtures\AttributeSignedIntegerNode;
use PHPUnit\Framework\TestCase;

final class GrammarPrinterTest extends TestCase
{
    public function testGrammarPrinterFormatsFoldRecipe(): void
    {
        $grammar = GrammarPrinter::format(AddExpressionNode::class);

        self::assertSame(implode(PHP_EOL, [
            'Start ::= AddExpressionNode',
            '',
            'AddExpressionNode ::= IntegerNode ((Plus) IntegerNode)*',
            'IntegerNode ::= <custom>',
        ]), $grammar);
    }

    public function testGrammarPrinterFormatsOneOfBetweenAndTerminalRecipes(): void
    {
        $grammar = GrammarPrinter::format(AttributeExpressionNodeInterface::class);

        self::assertSame(implode(PHP_EOL, [
            'Start ::= AttributeExpressionNodeInterface',
            '',
            'AttributeExpressionNodeInterface ::= AttributeGroupedIntegerNode | AttributeIntegerNode',
            'AttributeGroupedIntegerNode ::= OpenParen AttributeIntegerNode CloseParen',
            'AttributeIntegerNode ::= Integer',
        ]), $grammar);
    }

    public function testGrammarPrinterFormatsListBetweenRecipe(): void
    {
        $grammar = GrammarPrinter::format(AttributeIntegerListNode::class);

        self::assertSame(implode(PHP_EOL, [
            'Start ::= AttributeIntegerListNode',
            '',
            'AttributeIntegerListNode ::= OpenParen (AttributeIntegerNode (Comma AttributeIntegerNode)*)? CloseParen',
            'AttributeIntegerNode ::= Integer',
        ]), $grammar);
    }

    public function testGrammarPrinterFormatsOptionalManyAndSeparatedByRecipes(): void
    {
        self::assertSame(implode(PHP_EOL, [
            'Start ::= AttributeOptionalIntegerNode',
            '',
            'AttributeOptionalIntegerNode ::= AttributeIntegerNode?',
            'AttributeIntegerNode ::= Integer',
        ]), GrammarPrinter::format(AttributeOptionalIntegerNode::class));

        self::assertSame(implode(PHP_EOL, [
            'Start ::= AttributeManyIntegerNode',
            '',
            'AttributeManyIntegerNode ::= AttributeIntegerNode*',
            'AttributeIntegerNode ::= Integer',
        ]), GrammarPrinter::format(AttributeManyIntegerNode::class));

        self::assertSame(implode(PHP_EOL, [
            'Start ::= AttributeSeparatedIntegerNode',
            '',
            'AttributeSeparatedIntegerNode ::= (AttributeIntegerNode (Comma AttributeIntegerNode)*)?',
            'AttributeIntegerNode ::= Integer',
        ]), GrammarPrinter::format(AttributeSeparatedIntegerNode::class));
    }

    public function testGrammarPrinterUsesGrammarAttributeForCustomNodes(): void
    {
        $grammar = GrammarPrinter::format(AttributeGrammarNode::class);

        self::assertSame(implode(PHP_EOL, [
            'Start ::= AttributeGrammarNode',
            '',
            'AttributeGrammarNode ::= AttributeIntegerNode AttributeOptionalIntegerNode',
            'AttributeIntegerNode ::= Integer',
            'AttributeOptionalIntegerNode ::= AttributeIntegerNode?',
        ]), $grammar);
    }

    public function testGrammarPrinterFormatsSequenceRecipe(): void
    {
        self::assertSame(implode(PHP_EOL, [
            'Start ::= AttributeGroupedSequenceNode',
            '',
            'AttributeGroupedSequenceNode ::= OpenParen AttributeIntegerNode CloseParen',
            'AttributeIntegerNode ::= Integer',
        ]), GrammarPrinter::format(AttributeGroupedSequenceNode::class));

        self::assertSame(implode(PHP_EOL, [
            'Start ::= AttributeSignedIntegerNode',
            '',
            'AttributeSignedIntegerNode ::= (Plus | Minus) AttributeIntegerNode',
            'AttributeIntegerNode ::= Integer',
        ]), GrammarPrinter::format(AttributeSignedIntegerNode::class));
    }
}
