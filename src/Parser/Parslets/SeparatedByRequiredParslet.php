<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\Attributes\SeparatedByRequired;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class SeparatedByRequiredParslet implements ParsletInterface
{
    public function __construct(private SeparatedByRequired $separatedBy)
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): ?object
    {
        $position = $parser->tokens->save();
        $items = $parser->separatedBy(
            fn (Parser $parser): ?object => $parser->parseNode($this->separatedBy->node, report: false),
            $this->separatedBy->separator,
            $this->separatedBy->allowTrailingSeparator
        );

        if ($items === []) {
            $parser->tokens->restore($position);

            if (!$report) {
                return null;
            }

            $items = [$parser->parseNode($this->separatedBy->node, report: true)];
        }

        return $nodeClass->newInstance($items);
    }
}
