<?php

declare(strict_types=1);

namespace Lexicon\Parser;

use InvalidArgumentException;
use UnitEnum;

enum Part
{
    case Optional;
    case Many;
    case SeparatedBy;
    case SeparatedByRequired;
    case ListBetween;
    case ManyUntil;
    case ManyUntilRequired;
    case OptionalSequence;

    /**
     * @param list<mixed> $arguments
     */
    public function parse(Parser $parser, bool $report, array $arguments): ParseResult
    {
        return match ($this) {
            self::Optional => $this->parseOptional($parser, $arguments),
            self::Many => $this->parseMany($parser, $arguments),
            self::SeparatedBy => $this->parseSeparatedBy($parser, $arguments),
            self::SeparatedByRequired => $this->parseSeparatedByRequired($parser, $arguments),
            self::ListBetween => $this->parseListBetween($parser, $report, $arguments),
            self::ManyUntil => $this->parseManyUntil($parser, $arguments, required: false),
            self::ManyUntilRequired => $this->parseManyUntil($parser, $arguments, required: true),
            self::OptionalSequence => $this->parseOptionalSequence($parser, $arguments),
        };
    }

    /**
     * @param list<mixed> $arguments
     */
    private function parseOptional(Parser $parser, array $arguments): ParseResult
    {
        $part = $arguments[0] ?? throw new InvalidArgumentException('Optional part requires a parser part.');
        $position = $parser->tokens->save();
        $result = $parser->parsePart($part, report: false);

        if ($result->matched) {
            return $result;
        }

        $parser->tokens->restore($position);

        return ParseResult::match(null);
    }

    /**
     * @param list<mixed> $arguments
     */
    private function parseMany(Parser $parser, array $arguments): ParseResult
    {
        $part = $arguments[0] ?? throw new InvalidArgumentException('Many part requires a parser part.');

        return ParseResult::match($parser->many(
            fn (Parser $parser): mixed => $this->parseItem($parser, $part)
        ));
    }

    /**
     * @param list<mixed> $arguments
     */
    private function parseSeparatedBy(Parser $parser, array $arguments): ParseResult
    {
        $part = $arguments[0] ?? throw new InvalidArgumentException('SeparatedBy part requires a parser part.');
        $separator = $arguments[1] ?? throw new InvalidArgumentException('SeparatedBy part requires a separator.');
        if (!$separator instanceof UnitEnum) {
            throw new InvalidArgumentException('SeparatedBy separator must be an enum case.');
        }

        $allowTrailingSeparator = $arguments[2] ?? false;
        if (!is_bool($allowTrailingSeparator)) {
            throw new InvalidArgumentException('SeparatedBy allowTrailingSeparator must be a boolean.');
        }

        return ParseResult::match($parser->separatedBy(
            fn (Parser $parser): mixed => $this->parseItem($parser, $part),
            $separator,
            $allowTrailingSeparator
        ));
    }

    /**
     * @param list<mixed> $arguments
     */
    private function parseSeparatedByRequired(Parser $parser, array $arguments): ParseResult
    {
        $items = $this->parseSeparatedBy($parser, $arguments)->value;

        return $items === [] ? ParseResult::noMatch() : ParseResult::match($items);
    }

    /**
     * @param list<mixed> $arguments
     */
    private function parseListBetween(Parser $parser, bool $report, array $arguments): ParseResult
    {
        $part = $arguments[0] ?? throw new InvalidArgumentException('ListBetween part requires a parser part.');
        $separator = $arguments[1] ?? throw new InvalidArgumentException('ListBetween part requires a separator.');
        $open = $arguments[2] ?? throw new InvalidArgumentException('ListBetween part requires an opening token.');
        $close = $arguments[3] ?? throw new InvalidArgumentException('ListBetween part requires a closing token.');

        if (!$separator instanceof UnitEnum || !$open instanceof UnitEnum || !$close instanceof UnitEnum) {
            throw new InvalidArgumentException('ListBetween tokens must be enum cases.');
        }

        if (!$report && !$parser->tokens->check($open)) {
            return ParseResult::noMatch();
        }

        $allowTrailingSeparator = $arguments[4] ?? false;
        if (!is_bool($allowTrailingSeparator)) {
            throw new InvalidArgumentException('ListBetween allowTrailingSeparator must be a boolean.');
        }

        return ParseResult::match($parser->listBetween(
            $open,
            fn (Parser $parser): mixed => $this->parseItem($parser, $part),
            $separator,
            $close,
            $allowTrailingSeparator
        ));
    }

    /**
     * @param list<mixed> $arguments
     */
    private function parseManyUntil(Parser $parser, array $arguments, bool $required): ParseResult
    {
        $part = $arguments[0] ?? throw new InvalidArgumentException('ManyUntil part requires a parser part.');
        $stop = $arguments[1] ?? throw new InvalidArgumentException('ManyUntil part requires stop tokens.');
        if (!$stop instanceof UnitEnum && !is_array($stop)) {
            throw new InvalidArgumentException('ManyUntil stop must be an enum case or list of enum cases.');
        }

        $items = $parser->manyUntil(
            fn (Parser $parser): mixed => $this->parseItem($parser, $part),
            $stop
        );

        if ($required && $items === []) {
            return ParseResult::noMatch();
        }

        return ParseResult::match($items);
    }

    /**
     * @param list<mixed> $arguments
     */
    private function parseOptionalSequence(Parser $parser, array $arguments): ParseResult
    {
        $position = $parser->tokens->save();
        $values = [];

        foreach ($arguments as $part) {
            $result = $parser->parsePart($part, report: false);
            if (!$result->matched) {
                $parser->tokens->restore($position);

                return ParseResult::match(null);
            }

            $values[] = $result->value;
        }

        return ParseResult::match($values);
    }

    private function parseItem(Parser $parser, mixed $part): mixed
    {
        $result = $parser->parsePart($part, report: false);

        return $result->matched ? $result->value : null;
    }
}
