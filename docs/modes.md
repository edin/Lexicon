# Modes

Modes are user-defined enum cases. They let the scanner behave like a state machine.

```php
enum XmlMode
{
    case Text;
    case Tag;
}
```

Start scanning in a mode:

```php
$tokens = Lexer::from(XmlToken::class)
    ->startIn(XmlMode::Text)
    ->scan($xml);
```

## Enter

`enter` replaces the current mode:

```php
#[Symbol('<', in: XmlMode::Text, enter: XmlMode::Tag)]
case OpenTag;

#[Symbol('>', in: XmlMode::Tag, enter: XmlMode::Text)]
case TagClose;
```

## Push And Pop

Use `push` and `pop` for nested mode transitions:

```php
enum Mode
{
    case Code;
    case String;
}

#[Symbol('"', in: Mode::Code, push: Mode::String)]
case StringStart;

#[RegexPattern('/\A[^"]+/', in: Mode::String)]
case StringText;

#[Symbol('"', in: Mode::String, pop: true)]
case StringEnd;
```

The token records the mode it was scanned in:

```php
$token->mode;
```

If no mode is used, token mode is `null`.
