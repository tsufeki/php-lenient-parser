<?php

namespace PhpLenientParser;

use PhpParser\Node;
use PhpLenientParser\Expression\ExpressionParserInterface;
use PhpLenientParser\Statement\StatementParserInterface;

interface ParserStateInterface
{
    /**
     * @param string $option
     *
     * @return mixed
     */
    public function getOption($option);

    /**
     * @param int $count
     *
     * @return Token
     */
    public function lookAhead($count = 0);

    /**
     * @param int $tokenType If token doesn't match $tokenType, add an error.
     *
     * @return Token|null
     */
    public function eat($tokenType = null);

    /**
     * @param string $message
     * @param array $attributes
     */
    public function addError($message, array $attributes = []);

    /**
     * Set set location-related attributes on node so it encompases $start and $end.
     *
     * @param Node $node
     * @param Node|Token $start
     * @param Node|Token $end
     *
     * @return Node $node itself.
     */
    public function setAttributes(Node $node, $start, $end);

    /**
     * @return ExpressionParserInterface
     */
    public function getExpressionParser();

    /**
     * @return StatementParserInterface
     */
    public function getStatementParser();
}
