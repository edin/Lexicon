<?php

declare(strict_types=1);

namespace Lexicon\Parser;

use Lexicon\Lexer\Token;
use LogicException;
use UnitEnum;

final class TokenStream
{
    private int $position = 0;

    /**
     * @param non-empty-list<Token> $tokens
     */
    public function __construct(private readonly array $tokens)
    {
    }

    /**
     * @phpstan-impure
     */
    public function current(): Token
    {
        return $this->tokens[$this->position] ?? $this->tokens[array_key_last($this->tokens)];
    }

    /**
     * @phpstan-impure
     */
    public function peek(int $offset = 1): Token
    {
        $position = $this->position + $offset;

        return $this->tokens[$position] ?? $this->tokens[array_key_last($this->tokens)];
    }

    /**
     * @phpstan-impure
     */
    public function isAtEnd(): bool
    {
        return $this->current()->group->name === 'EndOfFile';
    }

    /**
     * @phpstan-impure
     */
    public function advance(): Token
    {
        $current = $this->current();

        if (!$this->isAtEnd()) {
            $this->position++;
        }

        return $current;
    }

    /**
     * @phpstan-impure
     */
    public function check(UnitEnum $type): bool
    {
        return $this->current()->type === $type;
    }

    /**
     * @phpstan-impure
     */
    public function currentIs(UnitEnum $type): bool
    {
        return $this->check($type);
    }

    /**
     * @phpstan-impure
     */
    public function peekIs(UnitEnum $type, int $offset = 1): bool
    {
        return $this->peek($offset)->type === $type;
    }

    /**
     * @param non-empty-list<UnitEnum> $types
     * @phpstan-impure
     */
    public function checkAny(array $types): bool
    {
        foreach ($types as $type) {
            if ($this->check($type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @phpstan-impure
     */
    public function match(UnitEnum $type): ?Token
    {
        if (!$this->check($type)) {
            return null;
        }

        return $this->advance();
    }

    /**
     * @param non-empty-list<UnitEnum> $types
     * @phpstan-impure
     */
    public function matchAny(array $types): ?Token
    {
        foreach ($types as $type) {
            $match = $this->match($type);
            if ($match !== null) {
                return $match;
            }
        }

        return null;
    }

    /**
     * @param UnitEnum|callable(): mixed ...$choices
     */
    public function oneOf(UnitEnum|callable ...$choices): mixed
    {
        foreach ($choices as $choice) {
            $position = $this->save();
            $result = $this->parseChoice($choice);

            if ($result !== null) {
                return $result;
            }

            $this->restore($position);
        }

        return null;
    }

    public function optional(UnitEnum|callable $choice): mixed
    {
        $position = $this->save();
        $result = $this->parseChoice($choice);

        if ($result !== null) {
            return $result;
        }

        $this->restore($position);

        return null;
    }

    /**
     * @return list<mixed>
     */
    public function many(UnitEnum|callable $choice): array
    {
        $items = [];

        while (!$this->isAtEnd()) {
            $position = $this->save();
            $result = $this->parseChoice($choice);

            if ($result === null) {
                $this->restore($position);
                break;
            }

            $items[] = $result;

            if ($this->save() === $position) {
                throw new LogicException('TokenStream many() choice must consume at least one token.');
            }
        }

        return $items;
    }

    /**
     * @return non-empty-list<mixed>|null
     */
    public function oneOrMore(UnitEnum|callable $choice): ?array
    {
        $items = $this->many($choice);

        return $items === [] ? null : $items;
    }

    public function save(): int
    {
        return $this->position;
    }

    public function restore(int $position): void
    {
        $this->position = max(0, min($position, count($this->tokens) - 1));
    }

    private function parseChoice(UnitEnum|callable $choice): mixed
    {
        return $choice instanceof UnitEnum ? $this->match($choice) : $choice();
    }
}
