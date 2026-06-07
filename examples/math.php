<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Lexicon\Lexer\Attributes\EndOfFile;
use Lexicon\Lexer\Attributes\Literal;
use Lexicon\Lexer\Attributes\Symbol;
use Lexicon\Lexer\Attributes\Trivia;
use Lexicon\Lexer\Attributes\Unknown;
use Lexicon\Lexer\Debug\TokenTable;
use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Matchers\NumberTokenMatcher;
use Lexicon\Lexer\Matchers\WhitespaceTokenMatcher;
use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\Between;
use Lexicon\Parser\Attributes\Fold;
use Lexicon\Parser\Attributes\OneOf;
use Lexicon\Parser\Attributes\Terminal;
use Lexicon\Parser\Debug\AstPrinter;
use Lexicon\Parser\Parser;

enum MathToken
{
    #[Literal(NumberTokenMatcher::class)]
    case Number;

    #[Symbol('+')]
    case Plus;

    #[Symbol('-')]
    case Minus;

    #[Symbol('*')]
    case Star;

    #[Symbol('/')]
    case Slash;

    #[Symbol('(')]
    case OpenParen;

    #[Symbol(')')]
    case CloseParen;

    #[Trivia(WhitespaceTokenMatcher::class)]
    case Whitespace;

    #[Unknown]
    case Unknown;

    #[EndOfFile]
    case EndOfFile;
}

interface MathExpressionNode
{
}

#[OneOf([
    GroupedExpressionNode::class,
    NumberNode::class,
])]
interface PrimaryExpressionNode extends MathExpressionNode
{
}

#[Terminal(MathToken::Number, 'Expected number.')]
final readonly class NumberNode implements PrimaryExpressionNode
{
    public function __construct(public Token $token)
    {
    }
}

#[Between(
    MathToken::OpenParen,
    AdditiveExpressionNode::class,
    MathToken::CloseParen,
    'Expected (.',
    'Expected ).'
)]
final readonly class GroupedExpressionNode implements PrimaryExpressionNode
{
    public function __construct(public MathExpressionNode $expression)
    {
    }
}

#[Fold(
    operators: [MathToken::Star, MathToken::Slash],
    operand: PrimaryExpressionNode::class
)]
final readonly class MultiplicativeExpressionNode implements MathExpressionNode
{
    public function __construct(
        public Token $operator,
        public MathExpressionNode $left,
        public MathExpressionNode $right
    ) {
    }
}

#[Fold(
    operators: [MathToken::Plus, MathToken::Minus],
    operand: MultiplicativeExpressionNode::class
)]
final readonly class AdditiveExpressionNode implements MathExpressionNode
{
    public function __construct(
        public Token $operator,
        public MathExpressionNode $left,
        public MathExpressionNode $right
    ) {
    }
}

final readonly class MathParser
{
    public static function parse(Parser $parser): MathExpressionNode
    {
        $expression = $parser->parse(AdditiveExpressionNode::class);
        $parser->expect(MathToken::EndOfFile, 'Expected end of expression.');

        return $expression;
    }
}

$source = '2 + 3 * (4 - 1) / 5';

$lexer = Lexer::from(MathToken::class);
$tokens = $lexer->scan($source);
$parser = Parser::fromTokens($tokens);
$expression = MathParser::parse($parser);

echo 'Source:' . PHP_EOL;
echo $source . PHP_EOL . PHP_EOL;

echo 'Tokens:' . PHP_EOL;
echo TokenTable::format($tokens, includeTrivia: false) . PHP_EOL . PHP_EOL;

echo 'AST:' . PHP_EOL;
echo AstPrinter::format($expression) . PHP_EOL;

if ($lexer->diagnostics->hasErrors() || $parser->diagnostics->hasErrors()) {
    echo PHP_EOL . 'Diagnostics:' . PHP_EOL;

    foreach ([...$lexer->diagnostics->all(), ...$parser->diagnostics->all()] as $diagnostic) {
        echo sprintf(
            '%s:%d:%d %s',
            $diagnostic->location->file->path,
            $diagnostic->location->line,
            $diagnostic->location->column,
            $diagnostic->message
        ) . PHP_EOL;
    }
}
