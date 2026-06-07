<?php

declare(strict_types=1);

namespace Lexicon\Lexer;

final readonly class Location
{
    public function __construct(
        public SourceFile $file,
        public int $position,
        public int $line,
        public int $column,
    ) {
    }

    /**
     * @return array{file: string, position: int, line: int, column: int}
     */
    public function __debugInfo(): array
    {
        return [
            'file' => $this->file->path,
            'position' => $this->position,
            'line' => $this->line,
            'column' => $this->column,
        ];
    }
}
