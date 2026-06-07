# Parser Primitives

Lexicon includes small parser primitives built on top of lexer tokens. The lexer remains independent.

## Parser And TokenStream

```php
use Lexicon\Parser\Parser;

$parser = Parser::fromTokens($tokens);

$parser->tokens->current();
$parser->tokens->peek();
$parser->tokens->match(MyToken::Plus);
$parser->tokens->save();
$parser->tokens->restore($position);
```

`Parser` also exposes diagnostics:

```php
$parser->diagnostics->all();
```

## Custom Parseable Nodes

Nodes can implement `ParseableNodeInterface`:

```php
use Lexicon\Parser\ParseableNodeInterface;
use Lexicon\Parser\Parser;

final readonly class IntegerNode implements ParseableNodeInterface
{
    public function __construct(public Token $token)
    {
    }

    public static function parse(Parser $parser): static
    {
        return new self(
            $parser->expect(MyToken::Integer, 'Expected integer.')
        );
    }
}
```

Custom nodes receive only the parser. The token stream is available through:

```php
$parser->tokens
```

This keeps custom parsers simple:

```php
final readonly class IntegerNode implements ParseableNodeInterface
{
    public static function parse(Parser $parser): static
    {
        return new self(
            $parser->expect(MyToken::Integer, 'Expected integer.')
        );
    }
}
```

## Fold

Use `fold()` to build nested AST nodes from repeated operators:

```php
use Lexicon\Lexer\Token;
use Lexicon\Parser\Associativity;

interface ExpressionNode
{
}

final readonly class IntegerNode implements ExpressionNode, ParseableNodeInterface
{
    // ...
}

final readonly class AddExpressionNode implements ExpressionNode
{
    public function __construct(
        public Token $operator,
        public ExpressionNode $left,
        public ExpressionNode $right
    ) {
    }
}

final readonly class SubtractExpressionNode implements ExpressionNode
{
    public function __construct(
        public Token $operator,
        public ExpressionNode $left,
        public ExpressionNode $right
    ) {
    }
}

final readonly class ExpressionParser
{
    public static function parse(Parser $parser): ExpressionNode
    {
        return $parser->fold(
            [MyToken::Plus, MyToken::Minus],
            fn (Parser $parser): ExpressionNode => $parser->parse(IntegerNode::class),
            fn (Token $operator, ExpressionNode $left, ExpressionNode $right): ExpressionNode => match ($operator->type) {
                MyToken::Plus => new AddExpressionNode($operator, $left, $right),
                MyToken::Minus => new SubtractExpressionNode($operator, $left, $right),
            },
            Associativity::Right
        );
    }
}
```

`fold()` parses the first operand, then keeps parsing more operands while any operator matches. With `Associativity::Right`, `1 + 2 + 3` becomes `AddExpressionNode(1, AddExpressionNode(2, 3))`. Without the associativity argument, it folds left: `AddExpressionNode(AddExpressionNode(1, 2), 3)`.

## Attribute Recipes

Parser attributes let a node class declare its own parsing recipe.

### Terminal

`#[Terminal]` consumes one token and forwards it to the node constructor:

```php
use Lexicon\Lexer\Token as LexerToken;
use Lexicon\Parser\Attributes\Terminal;

#[Terminal(MyToken::Integer, 'Expected integer.')]
final readonly class IntegerNode implements ExpressionNode
{
    public function __construct(public LexerToken $token)
    {
    }
}
```

### OneOf

`#[OneOf]` tries node recipes in order. It is useful on an interface:

```php
use Lexicon\Parser\Attributes\OneOf;

#[OneOf([
    GroupedExpressionNode::class,
    IntegerNode::class,
])]
interface ExpressionNode
{
}
```

### Between

`#[Between]` parses one node between opening and closing tokens:

```php
use Lexicon\Parser\Attributes\Between;

#[Between(MyToken::OpenParen, ExpressionNode::class, MyToken::CloseParen)]
final readonly class GroupedExpressionNode implements ExpressionNode
{
    public function __construct(public ExpressionNode $expression)
    {
    }
}
```

### ListBetween

`#[ListBetween]` parses a separated list between opening and closing tokens:

```php
use Lexicon\Parser\Attributes\ListBetween;

#[ListBetween(
    MyToken::OpenParen,
    ArgumentNode::class,
    MyToken::Comma,
    MyToken::CloseParen,
    allowTrailingSeparator: true
)]
final readonly class ArgumentListNode
{
    /**
     * @param list<ArgumentNode> $arguments
     */
    public function __construct(public array $arguments)
    {
    }
}
```

### Fold

`#[Fold]` builds a binary AST node by forwarding the fold result into the node constructor:

```php
use Lexicon\Lexer\Token;
use Lexicon\Parser\Associativity;
use Lexicon\Parser\Attributes\Fold;

#[Fold(
    operators: [MyToken::Plus, MyToken::Minus],
    operand: TermNode::class,
    associativity: Associativity::Left
)]
final readonly class BinaryExpressionNode implements ExpressionNode
{
    public function __construct(
        public Token $operator,
        public ExpressionNode $left,
        public ExpressionNode $right
    ) {
    }
}

$expression = $parser->parse(BinaryExpressionNode::class);
```

The node class does not need a `parse()` method. For custom logic, a node can still implement `ParseableNodeInterface`.

## Small Parser DSL

Parser helpers use regular PHP callables. A callable parser receives the current parser and returns a node/value, or `null` when it does not match.

### oneOf

Try parsers in order and return the first match. Failed alternatives restore the token position.

```php
$node = $parser->oneOf([
    fn (Parser $parser): ?ExpressionNode => $parser->tokens->check(MyToken::Integer)
        ? $parser->parse(IntegerNode::class)
        : null,
    fn (Parser $parser): ?ExpressionNode => $parser->tokens->check(MyToken::String)
        ? $parser->parse(StringNode::class)
        : null,
]);
```

### optional

Parse a value if it is present, otherwise return `null` and leave the stream where it was.

```php
$minus = $parser->optional(
    fn (Parser $parser): ?Token => $parser->tokens->match(MyToken::Minus)
);
```

### many

Parse zero or more items until the parser returns `null`.

```php
$members = $parser->many(
    fn (Parser $parser): ?MemberNode => $parser->tokens->check(MyToken::Identifier)
        ? $parser->parse(MemberNode::class)
        : null
);
```

### between

Parse content between required opening and closing tokens.

```php
$expression = $parser->between(
    MyToken::OpenParen,
    fn (Parser $parser): ExpressionNode => ExpressionParser::parse($parser),
    MyToken::CloseParen
);
```

### separatedBy

Parse a delimited list, such as function arguments or array items.

```php
$arguments = $parser->separatedBy(
    fn (Parser $parser): ?ExpressionNode => ExpressionParser::tryParse($parser),
    MyToken::Comma,
    allowTrailingSeparator: true
);
```

### listBetween

Parse a separated list enclosed by opening and closing tokens.

```php
$arguments = $parser->listBetween(
    MyToken::OpenParen,
    fn (Parser $parser): ?ExpressionNode => ExpressionParser::tryParse($parser),
    MyToken::Comma,
    MyToken::CloseParen,
    allowTrailingSeparator: true
);
```

## JSON Parser Example

The primitives are enough to parse JSON with a user-defined token enum:

```php
final readonly class JsonParser
{
    public static function tryParseValue(Parser $parser): ?JsonNode
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
        if (!$parser->tokens->check(JsonToken::LeftBrace)) {
            return null;
        }

        return new JsonObjectNode($parser->listBetween(
            JsonToken::LeftBrace,
            self::tryParseMember(...),
            JsonToken::Comma,
            JsonToken::RightBrace
        ));
    }

    private static function tryParseArray(Parser $parser): ?JsonArrayNode
    {
        if (!$parser->tokens->check(JsonToken::LeftBracket)) {
            return null;
        }

        return new JsonArrayNode($parser->listBetween(
            JsonToken::LeftBracket,
            self::tryParseValue(...),
            JsonToken::Comma,
            JsonToken::RightBracket
        ));
    }
}
```

These are intentionally low-level primitives. Attribute-driven parser rules can build on this layer later.

## AST Debugging

Use `AstPrinter` to inspect AST shape without source files, positions, trivia, or locations:

```php
use Lexicon\Parser\Debug\AstPrinter;

echo AstPrinter::format($node);
```

Example:

```txt
AddExpressionNode
  operator: Plus "+"
  left: IntegerNode "1"
  right: IntegerNode "2"
```
