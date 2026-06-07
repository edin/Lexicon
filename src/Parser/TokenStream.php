<?php

declare(strict_types=1);

namespace Lexicon\Parser;

use Lexicon\Lexer\Token;
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

    public function current(): Token
    {
        return $this->tokens[$this->position] ?? $this->tokens[array_key_last($this->tokens)];
    }

    public function peek(int $offset = 1): Token
    {
        $position = $this->position + $offset;

        return $this->tokens[$position] ?? $this->tokens[array_key_last($this->tokens)];
    }

    public function isAtEnd(): bool
    {
        return $this->current()->group->name === 'EndOfFile';
    }

    public function advance(): Token
    {
        $current = $this->current();

        if (!$this->isAtEnd()) {
            $this->position++;
        }

        return $current;
    }

    public function check(UnitEnum $type): bool
    {
        return $this->current()->type === $type;
    }

    public function match(UnitEnum $type): ?Token
    {
        if (!$this->check($type)) {
            return null;
        }

        return $this->advance();
    }

    public function save(): int
    {
        return $this->position;
    }

    public function restore(int $position): void
    {
        $this->position = max(0, min($position, count($this->tokens) - 1));
    }
}
