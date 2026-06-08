# Changelog

## 0.3.0

- Added parser primitives for recursive descent parsing.
- Added attribute grammar recipes: `Terminal`, `OneOf`, `Between`, `Optional`, `Many`, `SeparatedBy`, `ListBetween`, `Sequence`, and `Fold`.
- Added `ParseableNodeInterface` for custom parser escape hatches.
- Added `AstPrinter` for compact AST shape debugging.
- Added `GrammarPrinter` for BNF-like grammar output with an explicit `Start ::= ...` rule.
- Added parser docs and a math parser example.

## 0.2.0

- Added lexer modes and mode stack support.
- Added JSON and XML lexer matchers.
- Added token debugging table output.
- Added CI and PHPStan.

## 0.1.0

- Initial attribute-driven lexer with enum token definitions.
