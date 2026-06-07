<?php

declare(strict_types=1);

namespace Lexicon\Lexer;

use Lexicon\Lexer\Matchers\IdentifierTokenMatcher;
use Lexicon\Lexer\Matchers\TokenMatcherInterface;
use Lexicon\Lexer\Matchers\TextTokenMatcher;
use LogicException;

final class TokenDefinitions
{
    public function __construct(private readonly TokenMetadataProvider $metadataProvider)
    {
    }

    /**
     * @return list<TokenMatcherInterface>
     */
    public function all(): array
    {
        static $all = [];
        $cacheKey = spl_object_id($this->metadataProvider);

        if (!isset($all[$cacheKey])) {
            $all[$cacheKey] = $this->build();
        }

        return $all[$cacheKey];
    }

    /**
     * @return list<TokenMatcherInterface>
     */
    private function build(): array
    {
        $matchers = [];
        $metadata = $this->metadataProvider->matcherTokens();

        usort(
            $metadata,
            fn (TokenMetadata $left, TokenMetadata $right): int => self::matcherPriority($left) <=> self::matcherPriority($right)
        );

        foreach ($metadata as $token) {
            if ($token->group === TokenGroup::Identifier || $token->matcherClass === null) {
                continue;
            }

            $matchers[] = self::createMatcher($token);
        }

        foreach ($this->metadataProvider->fixedTextTokensByLength() as $token) {
            $matchers[] = new TextTokenMatcher($token);
        }

        foreach ($metadata as $token) {
            if ($token->group === TokenGroup::Identifier) {
                $matchers[] = new IdentifierTokenMatcher($token, $this->metadataProvider->keywordTypes());
                break;
            }
        }

        return $matchers;
    }

    private static function matcherPriority(TokenMetadata $metadata): int
    {
        return match ($metadata->group) {
            TokenGroup::Trivia => 0,
            TokenGroup::Literal => 1,
            default => 2,
        };
    }

    /**
     */
    private static function createMatcher(TokenMetadata $metadata): TokenMatcherInterface
    {
        $matcherClass = $metadata->matcherClass;
        $matcher = new $matcherClass($metadata);

        if (!$matcher instanceof TokenMatcherInterface) {
            throw new LogicException(sprintf("Could not create token matcher '%s'.", $matcherClass));
        }

        return $matcher;
    }
}
