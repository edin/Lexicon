<?php

declare(strict_types=1);

namespace Lexicon\Lexer;

final class DiagnosticBag
{
    /** @var list<Diagnostic> */
    private array $diagnostics = [];

    /**
     * @return list<Diagnostic>
     */
    public function all(): array
    {
        return $this->diagnostics;
    }

    public function hasErrors(): bool
    {
        return $this->diagnostics !== [];
    }

    public function report(Location $location, string $message): void
    {
        $this->diagnostics[] = new Diagnostic($location, $message);
    }
}
