<?php

declare(strict_types=1);

namespace Lexicon\Lexer;

use Lexicon\Lexer\Attributes\TokenAttribute;
use LogicException;
use ReflectionEnum;
use ReflectionAttribute;
use UnitEnum;

final class TokenMetadataProvider
{
    /** @var array<class-string<UnitEnum>, self> */
    private static array $providers = [];

    /**
     * @param class-string<UnitEnum> $tokenType
     */
    private function __construct(private readonly string $tokenType)
    {
    }

    /**
     * @param class-string<UnitEnum> $tokenType
     */
    public static function for(string $tokenType): self
    {
        if (!enum_exists($tokenType)) {
            throw new LogicException(sprintf("Token type '%s' must be an enum.", $tokenType));
        }

        return self::$providers[$tokenType] ??= new self($tokenType);
    }

    /**
     * @return list<TokenMetadata>
     */
    public function all(): array
    {
        static $all = [];

        if (!isset($all[$this->tokenType])) {
            $enum = new ReflectionEnum($this->tokenType);
            $all[$this->tokenType] = array_map(
                fn (UnitEnum $type): TokenMetadata => $this->read($enum, $type),
                $this->tokenType::cases()
            );
            $this->validate($all[$this->tokenType]);
        }

        return $all[$this->tokenType];
    }

    /**
     * @return array<string, TokenMetadata>
     */
    public function byType(): array
    {
        static $byType = [];

        if (!isset($byType[$this->tokenType])) {
            $byType[$this->tokenType] = [];
            foreach ($this->all() as $metadata) {
                $byType[$this->tokenType][$metadata->type->name] = $metadata;
            }
        }

        return $byType[$this->tokenType];
    }

    /**
     * @return array<string, UnitEnum>
     */
    public function keywordTypes(): array
    {
        static $keywordTypes = [];

        if (!isset($keywordTypes[$this->tokenType])) {
            $keywordTypes[$this->tokenType] = [];
            foreach ($this->all() as $metadata) {
                if ($metadata->group === TokenGroup::Keyword && $metadata->text !== null) {
                    $keywordTypes[$this->tokenType][$metadata->text] = $metadata->type;
                }
            }
        }

        return $keywordTypes[$this->tokenType];
    }

    /**
     * @return list<TokenMetadata>
     */
    public function fixedTextTokensByLength(): array
    {
        static $tokens = [];

        if (!isset($tokens[$this->tokenType])) {
            $tokens[$this->tokenType] = array_values(array_filter(
                $this->all(),
                fn (TokenMetadata $metadata): bool => $metadata->group !== TokenGroup::Keyword
                    && $metadata->text !== null
                    && $metadata->matcherClass === null
            ));

            usort(
                $tokens[$this->tokenType],
                fn (TokenMetadata $left, TokenMetadata $right): int => strlen($right->text ?? '')
                    <=> strlen($left->text ?? '')
            );
        }

        return $tokens[$this->tokenType];
    }

    /**
     * @return list<TokenMetadata>
     */
    public function matcherTokens(): array
    {
        static $tokens = [];

        if (!isset($tokens[$this->tokenType])) {
            $tokens[$this->tokenType] = array_values(array_filter(
                $this->all(),
                fn (TokenMetadata $metadata): bool => $metadata->matcherClass !== null
            ));
        }

        return $tokens[$this->tokenType];
    }

    public function unknown(): ?TokenMetadata
    {
        static $unknown = [];
        static $resolved = [];

        if (!isset($resolved[$this->tokenType])) {
            $unknown[$this->tokenType] = null;
            foreach ($this->all() as $metadata) {
                if ($metadata->group === TokenGroup::Unknown) {
                    $unknown[$this->tokenType] = $metadata;
                    break;
                }
            }

            $resolved[$this->tokenType] = true;
        }

        return $unknown[$this->tokenType];
    }

    public function endOfFile(): TokenMetadata
    {
        foreach ($this->all() as $metadata) {
            if ($metadata->group === TokenGroup::EndOfFile) {
                return $metadata;
            }
        }

        throw new LogicException(sprintf("Token enum '%s' is missing an end-of-file token.", $this->tokenType));
    }

    /**
     * @param ReflectionEnum<UnitEnum> $enum
     */
    private function read(ReflectionEnum $enum, UnitEnum $type): TokenMetadata
    {
        $case = $enum->getCase($type->name);
        $tokenAttributes = $case->getAttributes(TokenAttribute::class, ReflectionAttribute::IS_INSTANCEOF);

        if ($tokenAttributes === []) {
            throw new LogicException(sprintf("Token '%s' is missing %s.", $type->name, TokenAttribute::class));
        }

        /** @var TokenAttribute $token */
        $token = $tokenAttributes[0]->newInstance();

        return new TokenMetadata(
            $type,
            $token->text,
            $token->group,
            $token->matcherClass,
            $token->in,
            $token->enter,
            $token->push,
            $token->pop
        );
    }

    /**
     * @param list<TokenMetadata> $metadata
     */
    private function validate(array $metadata): void
    {
        $fixedTextByValueAndMode = [];
        $endOfFileTokens = [];
        $unknownTokens = [];
        $identifierTokens = [];
        $keywordTokens = [];

        foreach ($metadata as $token) {
            if ($token->text !== null && $token->group !== TokenGroup::Unknown) {
                if ($token->text === '') {
                    throw new LogicException(sprintf(
                        "Token enum '%s' has an empty fixed text on '%s'.",
                        $this->tokenType,
                        $token->type->name
                    ));
                }

                $fixedTextKey = $token->text . "\0" . ($token->in === null ? '<any>' : $token->in->name);
                if (isset($fixedTextByValueAndMode[$fixedTextKey])) {
                    throw new LogicException(sprintf(
                        "Token enum '%s' defines duplicate fixed text '%s' on '%s' and '%s'.",
                        $this->tokenType,
                        $token->text,
                        $fixedTextByValueAndMode[$fixedTextKey]->type->name,
                        $token->type->name
                    ));
                }

                $fixedTextByValueAndMode[$fixedTextKey] = $token;
            }

            match ($token->group) {
                TokenGroup::EndOfFile => $endOfFileTokens[] = $token,
                TokenGroup::Unknown => $unknownTokens[] = $token,
                TokenGroup::Identifier => $identifierTokens[] = $token,
                TokenGroup::Keyword => $token->matcherClass === null ? $keywordTokens[] = $token : null,
                default => null,
            };
        }

        if (count($endOfFileTokens) !== 1) {
            throw new LogicException(sprintf(
                "Token enum '%s' must define exactly one end-of-file token; found %d.",
                $this->tokenType,
                count($endOfFileTokens)
            ));
        }

        if (count($unknownTokens) > 1) {
            throw new LogicException(sprintf(
                "Token enum '%s' must define at most one unknown token; found %d.",
                $this->tokenType,
                count($unknownTokens)
            ));
        }

        if (count($identifierTokens) > 1) {
            throw new LogicException(sprintf(
                "Token enum '%s' must define at most one identifier token; found %d.",
                $this->tokenType,
                count($identifierTokens)
            ));
        }

        if ($keywordTokens !== [] && $identifierTokens === []) {
            throw new LogicException(sprintf(
                "Token enum '%s' defines keyword tokens but no identifier token.",
                $this->tokenType
            ));
        }
    }
}
