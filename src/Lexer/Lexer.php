<?php

declare(strict_types=1);

namespace Lexicon\Lexer;

final class Lexer
{
    private SourceFile $sourceFile;
    private string $input = '';
    private int $position = 0;
    private int $line = 1;
    private int $column = 1;
    private ?\UnitEnum $startMode = null;
    private ?\UnitEnum $currentMode = null;
    /** @var list<\UnitEnum|null> */
    private array $modeStack = [];
    private readonly TokenMetadataProvider $metadataProvider;
    private readonly TokenDefinitions $tokenDefinitions;

    public DiagnosticBag $diagnostics;

    private function __construct(TokenMetadataProvider $metadataProvider)
    {
        $this->sourceFile = new SourceFile('<memory>', '');
        $this->diagnostics = new DiagnosticBag();
        $this->metadataProvider = $metadataProvider;
        $this->tokenDefinitions = new TokenDefinitions($this->metadataProvider);
    }

    /**
     * @param class-string<\UnitEnum> $tokenType
     */
    public static function from(string $tokenType): self
    {
        return new self(TokenMetadataProvider::for($tokenType));
    }

    public function startIn(?\UnitEnum $mode): self
    {
        $this->startMode = $mode;
        $this->currentMode = $mode;

        return $this;
    }

    /**
     * @return list<Token>
     */
    public function scan(string|SourceFile $source): array
    {
        $this->reset(is_string($source) ? new SourceFile('<memory>', $source) : $source);

        return $this->tokenize();
    }

    private function reset(SourceFile $sourceFile): void
    {
        $this->sourceFile = $sourceFile;
        $this->input = $sourceFile->text;
        $this->position = 0;
        $this->line = 1;
        $this->column = 1;
        $this->currentMode = $this->startMode;
        $this->modeStack = [];
        $this->diagnostics = new DiagnosticBag();
    }

    public function input(): string
    {
        return $this->input;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function isAtEnd(): bool
    {
        return $this->position >= strlen($this->input);
    }

    public function current(): string
    {
        return $this->input[$this->position] ?? "\0";
    }

    public function remaining(): string
    {
        return $this->isAtEnd() ? '' : substr($this->input, $this->position);
    }

    public function location(): Location
    {
        return new Location($this->sourceFile, $this->position, $this->line, $this->column);
    }

    public function peek(int $offset = 1): string
    {
        return $this->input[$this->position + $offset] ?? "\0";
    }

    public function isAt(string $text): bool
    {
        return $text !== '' && strncmp($this->remaining(), $text, strlen($text)) === 0;
    }

    public function tryTake(string $text): bool
    {
        if (!$this->isAt($text)) {
            return false;
        }

        for ($i = 0, $length = strlen($text); $i < $length; $i++) {
            $this->advance();
        }

        return true;
    }

    /**
     * @param callable(string): bool $predicate
     */
    public function takeWhile(callable $predicate): string
    {
        $start = $this->position;

        while (!$this->isAtEnd() && $predicate($this->current())) {
            $this->advance();
        }

        return substr($this->input, $start, $this->position - $start);
    }

    public function takeUntil(string $text): string
    {
        $start = $this->position;

        while (!$this->isAtEnd() && !$this->isAt($text)) {
            $this->advance();
        }

        return substr($this->input, $start, $this->position - $start);
    }

    public function advance(): void
    {
        if ($this->current() === "\n") {
            $this->line++;
            $this->column = 1;
        } else {
            $this->column++;
        }

        $this->position++;
    }

    /**
     * @return list<Token>
     */
    private function tokenize(): array
    {
        $tokens = [];
        $leadingTrivia = [];

        while (!$this->isAtEnd()) {
            foreach ($this->tokenDefinitions->all() as $definition) {
                $savedPosition = $this->position;
                $savedLine = $this->line;
                $savedColumn = $this->column;
                $match = $definition->match($this);

                if ($match !== null) {
                    $metadata = $this->metadataProvider->byType()[$match->type->name];
                    if (!$this->canUseToken($metadata)) {
                        $this->position = $savedPosition;
                        $this->line = $savedLine;
                        $this->column = $savedColumn;
                        continue;
                    }

                    $match = $match->withMode($this->currentMode);
                    if ($match->group === TokenGroup::Trivia) {
                        $leadingTrivia[] = $match;
                    } else {
                        $tokens[] = $match->withLeadingTrivia($leadingTrivia);
                        $leadingTrivia = [];
                    }

                    $this->enterMode($metadata);
                    continue 2;
                }

                $this->position = $savedPosition;
                $this->line = $savedLine;
                $this->column = $savedColumn;
            }

            $location = $this->location();
            $value = $this->takeUnknownRun();
            $this->diagnostics->report($location, sprintf("Unexpected token '%s'.", $value));

            $unknown = $this->metadataProvider->unknown();
            if ($unknown !== null) {
                $tokens[] = (new Token($unknown->type, $value, $location, $unknown->group, $this->currentMode))->withLeadingTrivia($leadingTrivia);
                $leadingTrivia = [];
                $this->enterMode($unknown);
            }
        }

        $endOfFile = $this->metadataProvider->endOfFile();
        $tokens[] = new Token($endOfFile->type, '', $this->location(), $endOfFile->group, $this->currentMode, $leadingTrivia);

        return $tokens;
    }

    private function canUseToken(TokenMetadata $metadata): bool
    {
        return $metadata->in === null || $metadata->in === $this->currentMode;
    }

    private function enterMode(TokenMetadata $metadata): void
    {
        if ($metadata->pop) {
            $this->currentMode = array_pop($this->modeStack);
            return;
        }

        if ($metadata->push !== null) {
            $this->modeStack[] = $this->currentMode;
            $this->currentMode = $metadata->push;
            return;
        }

        if ($metadata->enter !== null) {
            $this->currentMode = $metadata->enter;
        }
    }

    private function takeUnknownRun(): string
    {
        $start = $this->position;
        $this->advance();

        while (!$this->isAtEnd() && !$this->canMatchAtCurrentPosition()) {
            $this->advance();
        }

        return substr($this->input, $start, $this->position - $start);
    }

    private function canMatchAtCurrentPosition(): bool
    {
        foreach ($this->tokenDefinitions->all() as $definition) {
            $savedPosition = $this->position;
            $savedLine = $this->line;
            $savedColumn = $this->column;

            $match = $definition->match($this);
            if ($match !== null) {
                $metadata = $this->metadataProvider->byType()[$match->type->name];
                if (!$this->canUseToken($metadata)) {
                    $match = null;
                }
            }

            $this->position = $savedPosition;
            $this->line = $savedLine;
            $this->column = $savedColumn;

            if ($match !== null) {
                return true;
            }
        }

        return false;
    }
}
