<?php

declare(strict_types=1);

namespace Lexicon\Lexer;

final readonly class SourceFile
{
    public function __construct(
        public string $path,
        public string $text,
    ) {
    }

    /**
     * @return array{path: string, length: int}
     */
    public function __debugInfo(): array
    {
        return [
            'path' => $this->path,
            'length' => strlen($this->text),
        ];
    }
}
