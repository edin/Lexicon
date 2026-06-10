<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Lexer\Token;
use Lexicon\Lexer\Lexer;
use Lexicon\Parser\Associativity;
use Lexicon\Parser\Parser;
use Lexicon\Tests\Fixtures\AddExpressionNode;
use Lexicon\Tests\Fixtures\AttributeDecliningFactoryIntegerNode;
use Lexicon\Tests\Fixtures\AttributeExpressionNodeInterface;
use Lexicon\Tests\Fixtures\AttributeGroupedIntegerNode;
use Lexicon\Tests\Fixtures\AttributeIntegerListNode;
use Lexicon\Tests\Fixtures\AttributeIntegerNode;
use Lexicon\Tests\Fixtures\AttributeIntegerOrSignedIntegerNode;
use Lexicon\Tests\Fixtures\AttributeManyIntegerNode;
use Lexicon\Tests\Fixtures\AttributeManyIntegerOrSignedIntegerNode;
use Lexicon\Tests\Fixtures\AttributeOptionalIntegerNode;
use Lexicon\Tests\Fixtures\AttributePartIntegerListNode;
use Lexicon\Tests\Fixtures\AttributePartManyUntilIntegerNode;
use Lexicon\Tests\Fixtures\AttributePartOptionalIntegerNode;
use Lexicon\Tests\Fixtures\AttributePartOptionalSignedIntegerSequenceNode;
use Lexicon\Tests\Fixtures\AttributePartRequiredSeparatedIntegerNode;
use Lexicon\Tests\Fixtures\AttributePrefixedSignedIntegerNode;
use Lexicon\Tests\Fixtures\AttributeSeparatedIntegerNode;
use Lexicon\Tests\Fixtures\AttributeSeparatedRequiredIntegerNode;
use Lexicon\Tests\Fixtures\AttributeGroupedSequenceNode;
use Lexicon\Tests\Fixtures\AttributeSignedIntegerNode;
use Lexicon\Tests\Fixtures\ExpressionNodeInterface;
use Lexicon\Tests\Fixtures\ExpressionTokenType;
use Lexicon\Tests\Fixtures\IntegerNode;
use Lexicon\Tests\Fixtures\SubtractExpressionNode;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    public function testCustomParseableNodeCanUseParserTokenStream(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1');
        $parser = Parser::fromTokens($tokens);

        $integer = $parser->parse(IntegerNode::class);

        self::assertInstanceOf(IntegerNode::class, $integer);
        self::assertSame('1', $integer->token->value);
        self::assertFalse($parser->diagnostics->hasErrors());
    }

    public function testParserCanFoldRightIntoAstTree(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 + 2 + 3');
        $parser = Parser::fromTokens($tokens);

        $expression = $parser->fold(
            ExpressionTokenType::Plus,
            fn (Parser $parser): ExpressionNodeInterface => $parser->parse(IntegerNode::class),
            self::combineBinaryExpression(...),
            Associativity::Right
        );

        self::assertInstanceOf(AddExpressionNode::class, $expression);
        self::assertIntegerNode('1', $expression->left);
        self::assertInstanceOf(AddExpressionNode::class, $expression->right);
        self::assertIntegerNode('2', $expression->right->left);
        self::assertIntegerNode('3', $expression->right->right);
    }

    public function testParserCanFoldLeftIntoAstTree(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 + 2 + 3');
        $parser = Parser::fromTokens($tokens);

        $expression = $parser->fold(
            ExpressionTokenType::Plus,
            fn (Parser $parser): ExpressionNodeInterface => $parser->parse(IntegerNode::class),
            self::combineBinaryExpression(...)
        );

        self::assertInstanceOf(AddExpressionNode::class, $expression);
        self::assertInstanceOf(AddExpressionNode::class, $expression->left);
        self::assertIntegerNode('1', $expression->left->left);
        self::assertIntegerNode('2', $expression->left->right);
        self::assertIntegerNode('3', $expression->right);
    }

    public function testParserCanUseFoldAttributeOnNodeClass(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 + 2 + 3');
        $parser = Parser::fromTokens($tokens);

        $expression = $parser->parse(AddExpressionNode::class);

        self::assertInstanceOf(AddExpressionNode::class, $expression);
        self::assertInstanceOf(AddExpressionNode::class, $expression->left);
        self::assertIntegerNode('1', $expression->left->left);
        self::assertIntegerNode('2', $expression->left->right);
        self::assertIntegerNode('3', $expression->right);
    }

    public function testParserCanUseTerminalAttributeOnNodeClass(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('123');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeIntegerNode::class);

        self::assertInstanceOf(AttributeIntegerNode::class, $node);
        self::assertSame('123', $node->token->value);
    }

    public function testParserCanUseBetweenAttributeOnNodeClass(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('(123)');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeGroupedIntegerNode::class);

        self::assertInstanceOf(AttributeGroupedIntegerNode::class, $node);
        self::assertSame('123', $node->node->token->value);
        self::assertTrue($parser->tokens->isAtEnd());
    }

    public function testParserCanUseListBetweenAttributeOnNodeClass(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('(1, 2, 3,)');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeIntegerListNode::class);

        self::assertInstanceOf(AttributeIntegerListNode::class, $node);
        self::assertSame(['1', '2', '3'], array_map(
            fn (AttributeIntegerNode $node): string => $node->token->value,
            $node->items
        ));
    }

    public function testParserCanUseOneOfAttributeOnNodeInterface(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('(123)');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeExpressionNodeInterface::class);

        self::assertInstanceOf(AttributeGroupedIntegerNode::class, $node);
        self::assertSame('123', $node->node->token->value);
    }

    public function testParserCanUseOptionalAttributeOnNodeClass(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('123');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeOptionalIntegerNode::class);

        self::assertInstanceOf(AttributeOptionalIntegerNode::class, $node);
        self::assertInstanceOf(AttributeIntegerNode::class, $node->node);
        self::assertSame('123', $node->node->token->value);
    }

    public function testParserCanUseOptionalAttributeWhenNodeIsMissing(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('+');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeOptionalIntegerNode::class);

        self::assertInstanceOf(AttributeOptionalIntegerNode::class, $node);
        self::assertNull($node->node);
        self::assertTrue($parser->tokens->check(ExpressionTokenType::Plus));
    }

    public function testParserCanUseManyAttributeOnNodeClass(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 2 3 +');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeManyIntegerNode::class);

        self::assertInstanceOf(AttributeManyIntegerNode::class, $node);
        self::assertSame(['1', '2', '3'], array_map(
            fn (AttributeIntegerNode $node): string => $node->token->value,
            $node->items
        ));
        self::assertTrue($parser->tokens->check(ExpressionTokenType::Plus));
    }

    public function testParserCanUseManyAttributeWithNodeAlternatives(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 - 2 3');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeManyIntegerOrSignedIntegerNode::class);

        self::assertInstanceOf(AttributeManyIntegerOrSignedIntegerNode::class, $node);
        self::assertInstanceOf(AttributeIntegerNode::class, $node->items[0]);
        self::assertInstanceOf(AttributeSignedIntegerNode::class, $node->items[1]);
        self::assertInstanceOf(AttributeIntegerNode::class, $node->items[2]);
    }

    public function testParserCanUseSeparatedByAttributeOnNodeClass(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1, 2, 3,');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeSeparatedIntegerNode::class);

        self::assertInstanceOf(AttributeSeparatedIntegerNode::class, $node);
        self::assertSame(['1', '2', '3'], array_map(
            fn (AttributeIntegerNode $node): string => $node->token->value,
            $node->items
        ));
        self::assertFalse($parser->diagnostics->hasErrors());
    }

    public function testParserCanUseSeparatedByRequiredAttributeOnNodeClass(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1, 2, 3,');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeSeparatedRequiredIntegerNode::class);

        self::assertInstanceOf(AttributeSeparatedRequiredIntegerNode::class, $node);
        self::assertSame(['1', '2', '3'], array_map(
            fn (AttributeIntegerNode $node): string => $node->token->value,
            $node->items
        ));
        self::assertFalse($parser->diagnostics->hasErrors());
    }

    public function testParserReportsWhenSeparatedByRequiredAttributeHasNoFirstItem(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('+');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeSeparatedRequiredIntegerNode::class);

        self::assertInstanceOf(AttributeSeparatedRequiredIntegerNode::class, $node);
        self::assertTrue($parser->diagnostics->hasErrors());
        self::assertSame('Expected integer.', $parser->diagnostics->all()[0]->message);
    }

    public function testParserCanUseSequenceAttributeOnNodeClass(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('(123)');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeGroupedSequenceNode::class);

        self::assertInstanceOf(AttributeGroupedSequenceNode::class, $node);
        self::assertSame(ExpressionTokenType::OpenParen, $node->open->type);
        self::assertSame('123', $node->node->token->value);
        self::assertSame(ExpressionTokenType::CloseParen, $node->close->type);
    }

    public function testParserCanUseSequenceAttributeWithTerminalAlternatives(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('-123');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeSignedIntegerNode::class);

        self::assertInstanceOf(AttributeSignedIntegerNode::class, $node);
        self::assertSame(ExpressionTokenType::Minus, $node->sign->type);
        self::assertSame('123', $node->number->token->value);
    }

    public function testParserCanUseRepeatableSequenceAttributesWithFactories(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('-123');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeIntegerOrSignedIntegerNode::class);

        self::assertInstanceOf(AttributeIntegerOrSignedIntegerNode::class, $node);
        self::assertSame(ExpressionTokenType::Minus, $node->sign?->type);
        self::assertSame('123', $node->number->token->value);
    }

    public function testParserCanUseFirstRepeatableSequenceFactoryWhenItMatches(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('123');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeIntegerOrSignedIntegerNode::class);

        self::assertInstanceOf(AttributeIntegerOrSignedIntegerNode::class, $node);
        self::assertNull($node->sign);
        self::assertSame('123', $node->number->token->value);
    }

    public function testParserTriesNextRepeatableSequenceWhenFactoryReturnsNull(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('123');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributeDecliningFactoryIntegerNode::class);

        self::assertInstanceOf(AttributeDecliningFactoryIntegerNode::class, $node);
        self::assertSame('123', $node->number->token->value);
    }

    public function testParserCanUseOptionalPartInSequenceAttribute(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('+');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributePartOptionalIntegerNode::class);

        self::assertInstanceOf(AttributePartOptionalIntegerNode::class, $node);
        self::assertNull($node->number);
        self::assertSame(ExpressionTokenType::Plus, $node->plus->type);
    }

    public function testParserCanUseListBetweenPartInSequenceAttribute(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('(1, 2, 3,)');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributePartIntegerListNode::class);

        self::assertInstanceOf(AttributePartIntegerListNode::class, $node);
        self::assertSame(['1', '2', '3'], array_map(
            fn (AttributeIntegerNode $node): string => $node->token->value,
            $node->items
        ));
    }

    public function testParserCanUseRequiredSeparatedByPartInSequenceAttribute(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1, 2, 3');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributePartRequiredSeparatedIntegerNode::class);

        self::assertInstanceOf(AttributePartRequiredSeparatedIntegerNode::class, $node);
        self::assertSame(['1', '2', '3'], array_map(
            fn (AttributeIntegerNode $node): string => $node->token->value,
            $node->items
        ));
    }

    public function testParserCanUseManyUntilPartInSequenceAttribute(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 2 3 +');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributePartManyUntilIntegerNode::class);

        self::assertInstanceOf(AttributePartManyUntilIntegerNode::class, $node);
        self::assertSame(['1', '2', '3'], array_map(
            fn (AttributeIntegerNode $node): string => $node->token->value,
            $node->items
        ));
        self::assertSame(ExpressionTokenType::Plus, $node->plus->type);
    }

    public function testParserCanUseOptionalSequencePartInSequenceAttribute(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('- 1,');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributePartOptionalSignedIntegerSequenceNode::class);

        self::assertInstanceOf(AttributePartOptionalSignedIntegerSequenceNode::class, $node);
        self::assertSame(ExpressionTokenType::Minus, $node->signed[0]->type);
        self::assertSame('1', $node->signed[1]->token->value);
        self::assertSame(ExpressionTokenType::Comma, $node->comma->type);
    }

    public function testParserCanUsePrefixManyWithSequenceAttribute(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 2 - 3');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->parse(AttributePrefixedSignedIntegerNode::class);

        self::assertInstanceOf(AttributePrefixedSignedIntegerNode::class, $node);
        self::assertSame(['1', '2'], array_map(
            fn (AttributeIntegerNode $node): string => $node->token->value,
            $node->prefixes
        ));
        self::assertSame(ExpressionTokenType::Minus, $node->sign->type);
        self::assertSame('3', $node->number->token->value);
    }

    public function testParserFoldReturnsFirstItemWhenSeparatorIsMissing(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1');
        $parser = Parser::fromTokens($tokens);

        $expression = $parser->fold(
            ExpressionTokenType::Plus,
            fn (Parser $parser): ExpressionNodeInterface => $parser->parse(IntegerNode::class),
            self::combineBinaryExpression(...)
        );

        self::assertIntegerNode('1', $expression);
    }

    public function testParserCanFoldAnyMatchingOperatorIntoDifferentAstNodes(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 + 2 - 3');
        $parser = Parser::fromTokens($tokens);

        $expression = $parser->fold(
            [ExpressionTokenType::Plus, ExpressionTokenType::Minus],
            fn (Parser $parser): ExpressionNodeInterface => $parser->parse(IntegerNode::class),
            self::combineBinaryExpression(...)
        );

        self::assertInstanceOf(SubtractExpressionNode::class, $expression);
        self::assertInstanceOf(AddExpressionNode::class, $expression->left);
        self::assertIntegerNode('1', $expression->left->left);
        self::assertIntegerNode('2', $expression->left->right);
        self::assertIntegerNode('3', $expression->right);
    }

    public function testParserCanChooseOneOfSeveralParsers(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->oneOf([
            fn (Parser $parser): ?ExpressionNodeInterface => $parser->tokens->match(ExpressionTokenType::Minus) === null
                ? null
                : $parser->parse(IntegerNode::class),
            self::parseOptionalInteger(...),
        ]);

        self::assertIntegerNode('1', $node);
        self::assertTrue($parser->tokens->isAtEnd());
    }

    public function testParserOptionalRestoresPositionWhenParserDoesNotMatch(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('+ 1');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->optional(self::parseOptionalInteger(...));

        self::assertNull($node);
        self::assertTrue($parser->tokens->check(ExpressionTokenType::Plus));
    }

    public function testParserManyParsesUntilParserDoesNotMatch(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 2 3 +');
        $parser = Parser::fromTokens($tokens);

        $nodes = $parser->many(self::parseOptionalInteger(...));

        self::assertSame(['1', '2', '3'], array_map(
            fn (IntegerNode $node): string => $node->token->value,
            $nodes
        ));
        self::assertTrue($parser->tokens->check(ExpressionTokenType::Plus));
    }

    public function testParserManyUntilStopsBeforeGivenTokens(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 2 3 +');
        $parser = Parser::fromTokens($tokens);

        $nodes = $parser->manyUntil(self::parseOptionalInteger(...), ExpressionTokenType::Plus);

        self::assertSame(['1', '2', '3'], array_map(
            fn (IntegerNode $node): string => $node->token->value,
            $nodes
        ));
        self::assertTrue($parser->tokens->check(ExpressionTokenType::Plus));
    }

    public function testParserBetweenParsesContentInsideTokens(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('(1)');
        $parser = Parser::fromTokens($tokens);

        $node = $parser->between(
            ExpressionTokenType::OpenParen,
            fn (Parser $parser): IntegerNode => $parser->parse(IntegerNode::class),
            ExpressionTokenType::CloseParen
        );

        self::assertIntegerNode('1', $node);
        self::assertTrue($parser->tokens->isAtEnd());
    }

    public function testParserSeparatedByParsesDelimitedItems(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1, 2, 3');
        $parser = Parser::fromTokens($tokens);

        $nodes = $parser->separatedBy(self::parseOptionalInteger(...), ExpressionTokenType::Comma);

        self::assertSame(['1', '2', '3'], array_map(
            fn (IntegerNode $node): string => $node->token->value,
            $nodes
        ));
        self::assertFalse($parser->diagnostics->hasErrors());
    }

    public function testParserSeparatedByCanAllowTrailingSeparator(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1, 2,');
        $parser = Parser::fromTokens($tokens);

        $nodes = $parser->separatedBy(
            self::parseOptionalInteger(...),
            ExpressionTokenType::Comma,
            allowTrailingSeparator: true
        );

        self::assertSame(['1', '2'], array_map(
            fn (IntegerNode $node): string => $node->token->value,
            $nodes
        ));
        self::assertFalse($parser->diagnostics->hasErrors());
    }

    public function testParserDelimitedParsesItemsBeforeCloseToken(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1, 2,)');
        $parser = Parser::fromTokens($tokens);

        $nodes = $parser->delimited(
            self::parseOptionalInteger(...),
            ExpressionTokenType::Comma,
            ExpressionTokenType::CloseParen
        );

        self::assertSame(['1', '2'], array_map(
            fn (IntegerNode $node): string => $node->token->value,
            $nodes
        ));
        self::assertTrue($parser->tokens->check(ExpressionTokenType::CloseParen));
        self::assertFalse($parser->diagnostics->hasErrors());
    }

    public function testParserCanParseListBetweenTokens(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('(1, 2, 3)');
        $parser = Parser::fromTokens($tokens);

        $nodes = $parser->listBetween(
            ExpressionTokenType::OpenParen,
            self::parseOptionalInteger(...),
            ExpressionTokenType::Comma,
            ExpressionTokenType::CloseParen
        );

        self::assertSame(['1', '2', '3'], array_map(
            fn (IntegerNode $node): string => $node->token->value,
            $nodes
        ));
        self::assertTrue($parser->tokens->isAtEnd());
    }

    public function testParserCanParseEmptyListBetweenTokens(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('()');
        $parser = Parser::fromTokens($tokens);

        $nodes = $parser->listBetween(
            ExpressionTokenType::OpenParen,
            self::parseOptionalInteger(...),
            ExpressionTokenType::Comma,
            ExpressionTokenType::CloseParen
        );

        self::assertSame([], $nodes);
        self::assertTrue($parser->tokens->isAtEnd());
    }

    public function testParserExpectReportsDiagnosticWhenTokenIsMissing(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('+');
        $parser = Parser::fromTokens($tokens);

        $parser->parse(IntegerNode::class);

        self::assertTrue($parser->diagnostics->hasErrors());
        self::assertSame('Expected integer.', $parser->diagnostics->all()[0]->message);
    }

    public function testTokenStreamCanSaveAndRestorePosition(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 + 2');
        $parser = Parser::fromTokens($tokens);
        $position = $parser->tokens->save();

        $first = $parser->tokens->advance();
        $parser->tokens->advance();
        $parser->tokens->restore($position);

        self::assertSame($first, $parser->tokens->current());
    }

    public function testTokenStreamCanCheckAndMatchSeveralTokenTypes(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 + 2');
        $stream = Parser::fromTokens($tokens)->tokens;

        self::assertTrue($stream->currentIs(ExpressionTokenType::Integer));
        self::assertTrue($stream->peekIs(ExpressionTokenType::Plus));
        self::assertTrue($stream->checkAny([ExpressionTokenType::Plus, ExpressionTokenType::Integer]));

        $integer = $stream->matchAny([ExpressionTokenType::Plus, ExpressionTokenType::Integer]);

        self::assertSame(ExpressionTokenType::Integer, $integer?->type);
        self::assertTrue($stream->currentIs(ExpressionTokenType::Plus));
    }

    public function testTokenStreamCanParseTokenOrCallableChoices(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 + 2');
        $stream = Parser::fromTokens($tokens)->tokens;

        $integer = $stream->oneOf(
            ExpressionTokenType::Plus,
            ExpressionTokenType::Integer
        );
        $plus = $stream->optional(ExpressionTokenType::Plus);
        $two = $stream->optional(fn (): ?Token => $stream->match(ExpressionTokenType::Integer));

        self::assertSame(ExpressionTokenType::Integer, $integer?->type);
        self::assertSame(ExpressionTokenType::Plus, $plus?->type);
        self::assertSame('2', $two?->value);
        self::assertTrue($stream->isAtEnd());
    }

    public function testTokenStreamCanParseManyAndOneOrMoreChoices(): void
    {
        $tokens = Lexer::from(ExpressionTokenType::class)->scan('1 2 +');
        $stream = Parser::fromTokens($tokens)->tokens;

        $integers = $stream->many(ExpressionTokenType::Integer);
        $plus = $stream->oneOrMore(ExpressionTokenType::Plus);
        $missing = $stream->oneOrMore(ExpressionTokenType::Minus);

        self::assertCount(2, $integers);
        self::assertSame('1', $integers[0]->value);
        self::assertSame('2', $integers[1]->value);
        self::assertCount(1, $plus);
        self::assertNull($missing);
    }

    private static function assertIntegerNode(string $value, ExpressionNodeInterface $node): void
    {
        self::assertInstanceOf(IntegerNode::class, $node);
        self::assertSame($value, $node->token->value);
    }

    private static function combineBinaryExpression(
        Token $operator,
        ExpressionNodeInterface $left,
        ExpressionNodeInterface $right
    ): ExpressionNodeInterface {
        return match ($operator->type) {
            ExpressionTokenType::Plus => new AddExpressionNode($operator, $left, $right),
            ExpressionTokenType::Minus => new SubtractExpressionNode($operator, $left, $right),
            default => self::fail(sprintf('Unexpected operator %s.', $operator->type->name)),
        };
    }

    private static function parseOptionalInteger(Parser $parser): ?IntegerNode
    {
        if (!$parser->tokens->check(ExpressionTokenType::Integer)) {
            return null;
        }

        return $parser->parse(IntegerNode::class);
    }
}
