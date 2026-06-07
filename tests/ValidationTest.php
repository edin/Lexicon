<?php

declare(strict_types=1);

namespace Lexicon\Tests;

use Lexicon\Lexer\Lexer;
use Lexicon\Tests\Fixtures\DuplicateSymbolTokenType;
use Lexicon\Tests\Fixtures\DuplicateUnknownTokenType;
use Lexicon\Tests\Fixtures\KeywordWithoutIdentifierTokenType;
use Lexicon\Tests\Fixtures\MissingEndOfFileTokenType;
use LogicException;
use PHPUnit\Framework\TestCase;

final class ValidationTest extends TestCase
{
    public function testValidationRejectsDuplicateFixedText(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("duplicate fixed text '='");

        Lexer::from(DuplicateSymbolTokenType::class)->scan('=');
    }

    public function testValidationRequiresExactlyOneEndOfFileToken(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('exactly one end-of-file token');

        Lexer::from(MissingEndOfFileTokenType::class)->scan('=');
    }

    public function testValidationRejectsDuplicateUnknownTokens(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('at most one unknown token');

        Lexer::from(DuplicateUnknownTokenType::class)->scan('?');
    }

    public function testValidationRequiresIdentifierWhenKeywordsExist(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('keyword tokens but no identifier token');

        Lexer::from(KeywordWithoutIdentifierTokenType::class)->scan('if');
    }
}
