<?php

declare(strict_types=1);

namespace Lexicon\Lexer\Matchers;

use Lexicon\Lexer\Lexer;
use Lexicon\Lexer\Token;
use Lexicon\Lexer\TokenMetadata;

final class JsonStringTokenMatcher implements ITokenMatcher
{
    public function __construct(private readonly TokenMetadata $metadata)
    {
    }

    public function match(Lexer $lexer): ?Token
    {
        if ($lexer->isAtEnd() || $lexer->current() !== '"') {
            return null;
        }

        $location = $lexer->location();
        $value = $lexer->current();
        $lexer->advance();

        while (!$lexer->isAtEnd()) {
            $char = $lexer->current();

            if ($char === '"') {
                $value .= $char;
                $lexer->advance();

                return new Token($this->metadata->type, $value, $location, $this->metadata->group);
            }

            if ($char === '\\') {
                $value .= $char;
                $lexer->advance();

                if ($lexer->isAtEnd()) {
                    break;
                }

                $escape = $lexer->current();
                $value .= $escape;
                $lexer->advance();

                if ($escape === 'u') {
                    for ($i = 0; $i < 4; $i++) {
                        if ($lexer->isAtEnd() || !ctype_xdigit($lexer->current())) {
                            $lexer->diagnostics->report($location, 'Invalid JSON unicode escape.');
                            return new Token($this->metadata->type, $value, $location, $this->metadata->group);
                        }

                        $value .= $lexer->current();
                        $lexer->advance();
                    }

                    continue;
                }

                if (!in_array($escape, ['"', '\\', '/', 'b', 'f', 'n', 'r', 't'], true)) {
                    $lexer->diagnostics->report($location, sprintf("Invalid JSON escape '\\%s'.", $escape));
                }

                continue;
            }

            if (ord($char) < 0x20) {
                $lexer->diagnostics->report($location, 'JSON strings cannot contain unescaped control characters.');
                return new Token($this->metadata->type, $value, $location, $this->metadata->group);
            }

            $value .= $char;
            $lexer->advance();
        }

        $lexer->diagnostics->report($location, 'Unterminated JSON string.');

        return new Token($this->metadata->type, $value, $location, $this->metadata->group);
    }
}
