# Lexicon

Attribute-driven PHP lexer and parser toolkit.

Lexicon lets you define tokens with PHP enums, parse into typed AST nodes, and inspect tokens, AST shape, and generated BNF-like grammar.

```php
$tokens = Lexer::from(MyToken::class)->scan($source);
$node = Parser::fromTokens($tokens)->parse(MyNode::class);
```

## Features

- enum-based token definitions with PHP attributes
- matcher-backed literals, fixed tokens, keywords, symbols, trivia, unknown tokens, and EOF
- trivia preservation with `Token::fullText()`
- user-defined lexer modes with enter/push/pop transitions
- diagnostics for lexer and parser errors
- recursive descent parser primitives
- attribute grammar recipes: `Terminal`, `OneOf`, `Between`, `Optional`, `Many`, `SeparatedBy`, `ListBetween`, `Sequence`, `Fold`
- custom parser escape hatch with `ParseableNodeInterface`
- token table, AST printer, and BNF grammar printer

## Install

```bash
composer require edin/lexicon
```

For local development:

```bash
composer install
```

## Quick Taste

```php
#[OneOf([
    GroupedExpressionNode::class,
    NumberNode::class,
])]
interface PrimaryExpressionNode extends ExpressionNode
{
}

#[Terminal(MathToken::Number)]
final readonly class NumberNode implements PrimaryExpressionNode
{
    public function __construct(public Token $token)
    {
    }
}

#[Fold(
    operators: [MathToken::Plus, MathToken::Minus],
    operand: TermNode::class
)]
final readonly class ExpressionNode
{
    public function __construct(
        public Token $operator,
        public Node $left,
        public Node $right
    ) {
    }
}
```

Generated grammar:

```txt
Start ::= AdditiveExpressionNode

AdditiveExpressionNode ::= MultiplicativeExpressionNode ((Plus | Minus) MultiplicativeExpressionNode)*
MultiplicativeExpressionNode ::= PrimaryExpressionNode ((Star | Slash) PrimaryExpressionNode)*
PrimaryExpressionNode ::= GroupedExpressionNode | NumberNode
GroupedExpressionNode ::= OpenParen AdditiveExpressionNode CloseParen
NumberNode ::= Number
```

## Documentation

- [Quick Start](docs/quick-start.md)
- [Token Attributes](docs/token-attributes.md)
- [Matchers](docs/matchers.md)
- [Modes](docs/modes.md)
- [Parser Primitives And Recipes](docs/parser.md)
- [Debugging Tokens](docs/debugging.md)
- [Examples](docs/examples.md)

## Run Examples

```bash
php examples/basic-scanner.php
php examples/json-scanner.php
php examples/xml-scanner.php
php examples/math.php
```

## Test

```bash
composer test
composer analyse
```

## License

MIT
