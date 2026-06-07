<?php

declare(strict_types=1);

namespace Lexicon\Lexer;

use UnitEnum;

final readonly class Token
{
    public UnitEnum $type;
    public TokenGroup $group;
    public string $value;
    public Location $location;
    public ?UnitEnum $mode;
    /** @var list<Token> */
    public array $leadingTrivia;

    /**
     * @param list<Token> $leadingTrivia
     */
    public function __construct(
        UnitEnum $type,
        string $value,
        Location $location,
        ?TokenGroup $group = null,
        ?UnitEnum $mode = null,
        array $leadingTrivia = [],
    ) {
        $this->type = $type;
        $this->group = $group ?? TokenMetadataProvider::for($type::class)->byType()[$type->name]->group;
        $this->value = $value;
        $this->location = $location;
        $this->mode = $mode;
        $this->leadingTrivia = $leadingTrivia;
    }

    /**
     * @param list<Token> $leadingTrivia
     */
    public function withLeadingTrivia(array $leadingTrivia): self
    {
        return new self($this->type, $this->value, $this->location, $this->group, $this->mode, $leadingTrivia);
    }

    public function withMode(?UnitEnum $mode): self
    {
        return new self($this->type, $this->value, $this->location, $this->group, $mode, $this->leadingTrivia);
    }

    public function fullText(): string
    {
        $text = '';

        foreach ($this->leadingTrivia as $trivia) {
            $text .= $trivia->value;
        }

        return $text . $this->value;
    }

    public function span(): Span
    {
        return new Span($this->location->position, strlen($this->value));
    }

    public function __toString(): string
    {
        return sprintf("%s '%s' at %d:%d", $this->type->name, $this->value, $this->location->line, $this->location->column);
    }

    /**
     * @return array{
     *     type: string,
     *     group: string,
     *     value: string,
     *     mode: string|null,
     *     location: Location,
     *     span: Span,
     *     leadingTrivia: list<array{type: string, value: string, span: Span}>
     * }
     */
    public function __debugInfo(): array
    {
        return [
            'type' => $this->type->name,
            'group' => $this->group->name,
            'value' => $this->value,
            'mode' => $this->mode?->name,
            'location' => $this->location,
            'span' => $this->span(),
            'leadingTrivia' => array_map(
                fn (Token $token): array => [
                    'type' => $token->type->name,
                    'value' => $token->value,
                    'span' => $token->span(),
                ],
                $this->leadingTrivia
            ),
        ];
    }
}
