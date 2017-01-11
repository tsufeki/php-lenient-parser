<?php

namespace PhpLenientParser;

use PhpParser\Node;
use PhpParser\Lexer;
use PhpParser\ErrorHandler;
use PhpParser\Error;
use PhpLenientParser\Expression\ExpressionParserInterface;
use PhpLenientParser\Statement\StatementParserInterface;

class ParserState implements ParserStateInterface
{
    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * @var array
     */
    private $options;

    /**
     * @var ExpressionParserInterface
     */
    private $expressionParser;

    /**
     * @var StatementParserInterface
     */
    private $statementParser;

    /**
     * @var \SplQueue
     */
    private $lookAheadQueue;

    /**
     * @param Lexer $lexer
     * @param ErrorHandler $errorHandler
     * @param array $options
     * @param ExpressionParserInterface $expressionParser
     * @param StatementParserInterface $statementParser
     */
    public function __construct(
        $lexer,
        ErrorHandler $errorHandler,
        array $options,
        ExpressionParserInterface $expressionParser,
        StatementParserInterface $statementParser
    ) {
        $this->lexer = $lexer;
        $this->errorHandler = $errorHandler;
        $this->options = $options;
        $this->expressionParser = $expressionParser;
        $this->statementParser = $statementParser;
        $this->lookAheadQueue = new \SplQueue();
    }

    public function getOption($option)
    {
        return isset($this->options[$option]) ? $this->options[$option] : false;
    }

    public function lookAhead($count = 0)
    {
        $toRead = $count + 1 - $this->lookAheadQueue->count();
        for ($i = 0; $i < $toRead; $i++) {
            $token = new Token();
            $token->type = $this->lexer->getNextToken($token->value, $token->startAttributes, $token->endAttributes);
            $this->lookAheadQueue->enqueue($token);
        }

        return $this->lookAheadQueue[$count];
    }

    public function eat($tokenType = null)
    {
        $token = $this->lookAhead();

        if ($tokenType !== null && $tokenType !== $token->type) {
            $this->addError(sprintf('Expected %s, got %s', $tokenType, $token->type), $token->getAttributes());
            return null;
        }

        $this->lookAheadQueue->dequeue();
        return $token;
    }

    public function addError($message, array $attributes = [])
    {
        $this->errorHandler->handleError(new Error($message, $attributes));
    }

    public function setAttributes(Node $node, $start, $end)
    {
        $startAttrs = $start->getAttributes();
        $endAttrs = $end->getAttributes();

        foreach (['startLine', 'startTokenPos', 'startFilePos'] as $attr) {
            if (isset($startAttrs[$attr])) {
                $node->setAttribute($attr, $startAttrs[$attr]);
            }
        }

        foreach (['endLine', 'endTokenPos', 'endFilePos'] as $attr) {
            if (isset($endAttrs[$attr])) {
                $node->setAttribute($attr, $endAttrs[$attr]);
            }
        }

        return $node;
    }

    public function getExpressionParser()
    {
        return $this->expressionParser;
    }

    public function getStatementParser()
    {
        return $this->statementParser;
    }
}
