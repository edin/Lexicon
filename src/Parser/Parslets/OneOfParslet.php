<?php

declare(strict_types=1);

namespace Lexicon\Parser\Parslets;

use Lexicon\Parser\Attributes\OneOf;
use Lexicon\Parser\Parser;
use Lexicon\Parser\ParsletInterface;
use ReflectionClass;

final readonly class OneOfParslet implements ParsletInterface
{
    public function __construct(private OneOf $oneOf)
    {
    }

    /**
     * @param ReflectionClass<object> $nodeClass
     */
    public function parse(Parser $parser, ReflectionClass $nodeClass, bool $report): ?object
    {
        foreach ($this->oneOf->nodes as $nodeClass) {
            $position = $parser->tokens->save();
            $node = $parser->parseNode($nodeClass, report: false);

            if ($node !== null) {
                return $node;
            }

            $parser->tokens->restore($position);
        }

        if ($report) {
            $parser->diagnostics->report($parser->tokens->current()->location, $this->oneOf->message);
        }

        return null;
    }
}
