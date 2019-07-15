<?php declare(strict_types=1);

namespace PhpLenientParser;

use PhpLenientParser\Expression\ExpressionParserInterface;
use PhpLenientParser\Statement\StatementParserInterface;
use PhpParser\Node;

interface ParserStateInterface
{
    /**
     * @return mixed
     */
    public function getOption(string $option);

    public function lookAhead(int $count = 0): Token;

    /**
     * Check if next token matches any of given types. Don't eat it.
     */
    public function isNext(int ...$tokenTypes): bool;

    public function eat(): Token;

    /**
     * @param int $tokenType If not null and token doesn't match it, don't eat anything.
     */
    public function eatIf(int $tokenType = null): ?Token;

    /**
     * If token matches type, eat it; otherwise add an error.
     *
     * @return bool True if token was eaten.
     */
    public function assert(int $tokenType): bool;

    /**
     * Add an error for token.
     */
    public function unexpected(Token $token, int ...$expected): void;

    public function last(): Token;

    public function addError(string $message, array $attributes = []): void;

    /**
     * Set set location-related attributes on node so it encompases $start and $end.
     *
     * @param Node|Token $start
     * @param Node|Token $end
     */
    public function setAttributes(Node $node, $start, $end): void;

    public function getExpressionParser(): ExpressionParserInterface;

    public function getStatementParser(): StatementParserInterface;
}
