<?php

namespace PhpLenientParser;

use PhpLenientParser\Expression\ExpressionParserInterface;
use PhpLenientParser\Statement\StatementParserInterface;
use PhpParser\Node;

interface ParserStateInterface
{
    /**
     * @param string $option
     *
     * @return mixed
     */
    public function getOption(string $option);

    /**
     * @param int $count
     *
     * @return Token
     */
    public function lookAhead(int $count = 0): Token;

    /**
     * Check if next token matches any of given types. Don't eat it.
     *
     * @param int[] $tokenTypes
     *
     * @return bool
     */
    public function isNext(int ...$tokenTypes): bool;

    /**
     * @param int $tokenType If not null and token doesn't match it, don't eat anything.
     *
     * @return Token|null
     */
    public function eat(int $tokenType = null);

    /**
     * If token matches type, eat it; otherwise add an error.
     *
     * @param int $tokenType
     *
     * @return bool True if token was eaten.
     */
    public function assert(int $tokenType): bool;

    /**
     * Add an error for token.
     *
     * @param Token    $token
     * @param int|null $expected
     */
    public function unexpected(Token $token, int $expected = null);

    /**
     * @return Token|null
     */
    public function last();

    /**
     * @param string $message
     * @param array  $attributes
     */
    public function addError(string $message, array $attributes = []);

    /**
     * Set set location-related attributes on node so it encompases $start and $end.
     *
     * @param Node       $node
     * @param Node|Token $start
     * @param Node|Token $end
     *
     * @return Node $node itself.
     */
    public function setAttributes(Node $node, $start, $end): Node;

    /**
     * @return ExpressionParserInterface
     */
    public function getExpressionParser(): ExpressionParserInterface;

    /**
     * @return StatementParserInterface
     */
    public function getStatementParser(): StatementParserInterface;
}
