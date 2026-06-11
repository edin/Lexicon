<?php

declare(strict_types=1);

namespace Lexicon\Parser;

use Lexicon\Lexer\DiagnosticBag;
use Lexicon\Lexer\Token;
use Lexicon\Parser\Attributes\Sequence as SequenceAttribute;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use UnitEnum;

final class Parser
{
    public readonly TokenStream $tokens;
    public readonly DiagnosticBag $diagnostics;
    public readonly ParsletFactoryInterface $parslets;

    /**
     * @param non-empty-list<Token> $tokens
     */
    private function __construct(array $tokens, ?ParsletFactoryInterface $parslets = null)
    {
        $this->tokens = new TokenStream($tokens);
        $this->diagnostics = new DiagnosticBag();
        $this->parslets = $parslets ?? new DefaultParsletFactory();
    }

    /**
     * @param non-empty-list<Token> $tokens
     */
    public static function fromTokens(array $tokens, ?ParsletFactoryInterface $parslets = null): self
    {
        return new self($tokens, $parslets);
    }

    /**
     * @template T of object
     * @param class-string<T> $nodeClass
     * @return T
     */
    public function parse(string $nodeClass): object
    {
        $node = $this->parseNode($nodeClass, report: true);
        if ($node !== null) {
            return $node;
        }

        throw new LogicException(sprintf("Node class '%s' could not be parsed.", $nodeClass));
    }

    /**
     * @template T of object
     * @param class-string<T> $nodeClass
     * @return T|null
     */
    public function parseNode(string $nodeClass, bool $report): ?object
    {
        $reflection = new ReflectionClass($nodeClass);
        $parslet = $this->parslets->forNode($reflection);
        if ($parslet !== null) {
            return $parslet->parse($this, $reflection, $report);
        }

        if (!$report) {
            return null;
        }

        throw new LogicException(sprintf(
            "Node class '%s' must define a parser attribute or implement %s.",
            $nodeClass,
            ParseableNodeInterface::class
        ));
    }

    public function expect(UnitEnum $type, string $message): Token
    {
        $match = $this->tokens->match($type);
        if ($match !== null) {
            return $match;
        }

        $current = $this->tokens->current();
        $this->diagnostics->report($current->location, $message);

        return $current;
    }

    /**
     * @template T of object
     * @param class-string<T> $nodeClass
     * @return T|null
     */
    public function tryParse(string $nodeClass): ?object
    {
        return $this->parseNode($nodeClass, report: false);
    }

    /**
     * @template T
     * @param non-empty-list<callable(self): ?T> $parsers
     * @return T|null
     */
    public function oneOf(array $parsers): mixed
    {
        foreach ($parsers as $parser) {
            $position = $this->tokens->save();
            $result = $parser($this);

            if ($result !== null) {
                return $result;
            }

            $this->tokens->restore($position);
        }

        return null;
    }

    /**
     * @template T
     * @param callable(self): ?T $parser
     * @return T|null
     */
    public function optional(callable $parser): mixed
    {
        $position = $this->tokens->save();
        $result = $parser($this);

        if ($result !== null) {
            return $result;
        }

        $this->tokens->restore($position);

        return null;
    }

    /**
     * @template T
     * @param callable(self): ?T $parser
     * @return list<T>
     */
    public function many(callable $parser): array
    {
        $items = [];

        while (!$this->tokens->isAtEnd()) {
            $position = $this->tokens->save();
            $result = $parser($this);

            if ($result === null) {
                $this->tokens->restore($position);
                break;
            }

            $items[] = $result;

            if ($this->tokens->save() === $position) {
                throw new LogicException('Parser many() parser must consume at least one token.');
            }
        }

        return $items;
    }

    /**
     * @template T
     * @param callable(self): ?T $parser
     * @param UnitEnum|non-empty-list<UnitEnum> $stop
     * @return list<T>
     */
    public function manyUntil(callable $parser, UnitEnum|array $stop): array
    {
        $stop = $this->normalizeTokens($stop);
        $items = [];

        while (!$this->tokens->isAtEnd() && !$this->tokens->checkAny($stop)) {
            $position = $this->tokens->save();
            $result = $parser($this);

            if ($result === null) {
                $this->tokens->restore($position);
                break;
            }

            $items[] = $result;

            if ($this->tokens->save() === $position) {
                throw new LogicException('Parser manyUntil() parser must consume at least one token.');
            }
        }

        return $items;
    }

    /**
     * @template T
     * @param callable(self): T $parser
     * @return T
     */
    public function between(
        UnitEnum $open,
        callable $parser,
        UnitEnum $close,
        string $openMessage = 'Expected opening token.',
        string $closeMessage = 'Expected closing token.'
    ): mixed {
        $this->expect($open, $openMessage);
        $result = $parser($this);
        $this->expect($close, $closeMessage);

        return $result;
    }

    /**
     * @template T
     * @param callable(self): ?T $parser
     * @return list<T>
     */
    public function separatedBy(
        callable $parser,
        UnitEnum $separator,
        bool $allowTrailingSeparator = false
    ): array {
        $first = $this->optional($parser);
        if ($first === null) {
            return [];
        }

        $items = [$first];

        while ($this->tokens->match($separator) !== null) {
            $item = $this->optional($parser);
            if ($item === null) {
                if (!$allowTrailingSeparator) {
                    $this->diagnostics->report($this->tokens->current()->location, 'Expected item after separator.');
                }

                break;
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @template T
     * @param callable(self): ?T $parser
     * @return list<T>
     */
    public function delimited(
        callable $parser,
        UnitEnum $separator,
        UnitEnum $close,
        bool $allowTrailingSeparator = true
    ): array {
        $items = [];

        if ($this->tokens->check($close)) {
            return $items;
        }

        $first = $this->optional($parser);
        if ($first === null) {
            return $items;
        }

        $items[] = $first;

        while ($this->tokens->match($separator) !== null) {
            if ($this->tokens->check($close)) {
                if (!$allowTrailingSeparator) {
                    $this->diagnostics->report($this->tokens->current()->location, 'Expected item after separator.');
                }

                break;
            }

            $item = $this->optional($parser);
            if ($item === null) {
                if (!$allowTrailingSeparator) {
                    $this->diagnostics->report($this->tokens->current()->location, 'Expected item after separator.');
                }

                break;
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @template T
     * @param callable(self): ?T $itemParser
     * @return list<T>
     */
    public function listBetween(
        UnitEnum $open,
        callable $itemParser,
        UnitEnum $separator,
        UnitEnum $close,
        bool $allowTrailingSeparator = false,
        string $openMessage = 'Expected opening token.',
        string $closeMessage = 'Expected closing token.'
    ): array {
        return $this->between(
            $open,
            fn (self $parser): array => $parser->separatedBy($itemParser, $separator, $allowTrailingSeparator),
            $close,
            $openMessage,
            $closeMessage
        );
    }

    /**
     * @template T
     * @param UnitEnum|non-empty-list<UnitEnum> $operators
     * @param callable(self): T $parseOperand
     * @param callable(Token, T, T): T $combine
     * @return T
     */
    public function fold(
        UnitEnum|array $operators,
        callable $parseOperand,
        callable $combine,
        Associativity $associativity = Associativity::Left
    ): mixed {
        $operators = $this->normalizeOperators($operators);
        $operands = [$parseOperand($this)];
        $operatorTokens = [];

        while (($operatorToken = $this->matchAny($operators)) !== null) {
            $operatorTokens[] = $operatorToken;
            $operands[] = $parseOperand($this);
        }

        if ($associativity === Associativity::Right) {
            return $this->foldRight($operands, $operatorTokens, $combine);
        }

        return $this->foldLeft($operands, $operatorTokens, $combine);
    }

    /**
     * @template T
     * @param non-empty-list<T> $operands
     * @param list<Token> $operators
     * @param callable(Token, T, T): T $combine
     * @return T
     */
    private function foldLeft(array $operands, array $operators, callable $combine): mixed
    {
        $node = $operands[0];

        foreach ($operators as $index => $operator) {
            $node = $combine($operator, $node, $operands[$index + 1]);
        }

        return $node;
    }

    /**
     * @template T
     * @param non-empty-list<T> $operands
     * @param list<Token> $operators
     * @param callable(Token, T, T): T $combine
     * @return T
     */
    private function foldRight(array $operands, array $operators, callable $combine): mixed
    {
        $node = $operands[array_key_last($operands)];

        for ($index = count($operators) - 1; $index >= 0; $index--) {
            $node = $combine($operators[$index], $operands[$index], $node);
        }

        return $node;
    }

    /**
     * @param class-string<object>|non-empty-list<class-string<object>> $node
     */
    public function parseManyNode(string|array $node): ?object
    {
        if (is_string($node)) {
            return $this->parseNode($node, report: false);
        }

        foreach ($node as $nodeClass) {
            $position = $this->tokens->save();
            $parsed = $this->parseNode($nodeClass, report: false);

            if ($parsed !== null) {
                return $parsed;
            }

            $this->tokens->restore($position);
        }

        return null;
    }

    /**
     * @template T of object
     * @param ReflectionClass<T> $nodeClass
     * @param list<\ReflectionAttribute<SequenceAttribute>> $sequenceAttributes
     * @param list<mixed> $prefixValues
     * @return T|null
     */
    public function parseSequenceAlternatives(
        ReflectionClass $nodeClass,
        array $sequenceAttributes,
        bool $report,
        array $prefixValues = []
    ): ?object
    {
        $position = $this->tokens->save();

        foreach ($sequenceAttributes as $sequenceAttribute) {
            $this->tokens->restore($position);
            $node = $this->parseSequence($nodeClass, $sequenceAttribute->newInstance(), false, $prefixValues);

            if ($node !== null) {
                return $node;
            }
        }

        $this->tokens->restore($position);

        if ($report) {
            return $this->parseSequence($nodeClass, $sequenceAttributes[0]->newInstance(), true, $prefixValues);
        }

        return null;
    }

    /**
     * @template T of object
     * @param ReflectionClass<T> $nodeClass
     * @param list<mixed> $prefixValues
     * @return T|null
     */
    public function parseSequence(
        ReflectionClass $nodeClass,
        SequenceAttribute $sequence,
        bool $report,
        array $prefixValues = []
    ): ?object {
        $position = $this->tokens->save();
        $values = $prefixValues;

        foreach ($sequence->parts as $part) {
            $result = $this->parsePart($part, $report);

            if (!$result->matched) {
                $this->tokens->restore($position);

                return null;
            }

            $values[] = $result->value;
        }

        if ($sequence->factory !== null) {
            return $nodeClass->getMethod($sequence->factory)->invoke(null, ...$values);
        }

        return $nodeClass->newInstanceArgs($values);
    }

    /**
     * @param UnitEnum|class-string<object>|non-empty-list<UnitEnum>|array{0: Part, ...} $part
     */
    public function parsePart(UnitEnum|string|array $part, bool $report): ParseResult
    {
        if ($part instanceof UnitEnum) {
            $token = $report
                ? $this->expect($part, sprintf('Expected %s.', $part->name))
                : $this->tokens->match($part);

            return $token === null ? ParseResult::noMatch() : ParseResult::match($token);
        }

        if (is_string($part)) {
            $node = $this->parseNode($part, $report);

            return $node === null ? ParseResult::noMatch() : ParseResult::match($node);
        }

        $first = $part[0];
        if ($first instanceof Part) {
            return $first->parse($this, $report, array_slice($part, 1));
        }

        return $this->parseSequenceTerminalChoice($part, $report);
    }

    /**
     * @param non-empty-list<UnitEnum> $terminals
     */
    private function parseSequenceTerminalChoice(array $terminals, bool $report): ParseResult
    {
        $match = $this->matchAny($terminals);
        if ($match !== null) {
            return ParseResult::match($match);
        }

        if (!$report) {
            return ParseResult::noMatch();
        }

        $this->diagnostics->report(
            $this->tokens->current()->location,
            sprintf('Expected one of: %s.', implode(', ', array_map(
                fn (UnitEnum $terminal): string => $terminal->name,
                $terminals
            )))
        );

        return ParseResult::match($this->tokens->current());
    }

    /**
     * @param UnitEnum|array<array-key, mixed> $operators
     * @return non-empty-list<UnitEnum>
     */
    private function normalizeOperators(UnitEnum|array $operators): array
    {
        return $this->normalizeTokens($operators, 'Parser fold requires at least one operator.', 'Parser fold operators must be enum cases.');
    }

    /**
     * @param UnitEnum|array<array-key, mixed> $tokens
     * @return non-empty-list<UnitEnum>
     */
    private function normalizeTokens(
        UnitEnum|array $tokens,
        string $emptyMessage = 'Expected at least one token.',
        string $invalidMessage = 'Expected enum cases.'
    ): array {
        if ($tokens instanceof UnitEnum) {
            return [$tokens];
        }

        if ($tokens === []) {
            throw new InvalidArgumentException($emptyMessage);
        }

        $normalized = [];
        foreach ($tokens as $token) {
            if (!$token instanceof UnitEnum) {
                throw new InvalidArgumentException($invalidMessage);
            }

            $normalized[] = $token;
        }

        return $normalized;
    }

    /**
     * @param non-empty-list<UnitEnum> $operators
     */
    private function matchAny(array $operators): ?Token
    {
        return $this->tokens->matchAny($operators);
    }
}
