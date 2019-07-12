<?php declare(strict_types=1);

namespace PhpLenientParser;

use PhpLenientParser\Expression\ExpressionParserInterface;
use PhpLenientParser\Statement\StatementParserInterface;
use PhpParser\Error;
use PhpParser\ErrorHandler;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

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
     * @var Token|null
     */
    private $last;

    public function __construct(
        Lexer $lexer,
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

    public function getOption(string $option)
    {
        return isset($this->options[$option]) ? $this->options[$option] : false;
    }

    public function lookAhead(int $count = 0): Token
    {
        $toRead = $count + 1 - $this->lookAheadQueue->count();
        for ($i = 0; $i < $toRead; $i++) {
            $token = new Token();
            $token->type = $this->lexer->getNextToken($token->value, $token->startAttributes, $token->endAttributes);
            if ($token->type === Tokens::T_HALT_COMPILER) {
                $this->handleHaltCompiler($token);
            }
            $this->lookAheadQueue->enqueue($token);
        }

        return $this->lookAheadQueue[$count];
    }

    public function isNext(int ...$tokenTypes): bool
    {
        return in_array($this->lookAhead()->type, $tokenTypes);
    }

    public function eat(): Token
    {
        $token = $this->lookAhead();
        $this->last = $token;
        $this->lookAheadQueue->dequeue();

        return $token;
    }

    public function eatIf(int $tokenType = null): ?Token
    {
        $token = $this->lookAhead();

        if ($tokenType !== null && $tokenType !== $token->type) {
            return null;
        }

        return $this->eat();
    }

    public function assert(int $tokenType): bool
    {
        $token = $this->lookAhead();

        if ($tokenType !== $token->type) {
            $this->unexpected($token, $tokenType);

            return false;
        }

        $this->last = $token;
        $this->lookAheadQueue->dequeue();

        return true;
    }

    public function unexpected(Token $token, ?int $expected = null): void
    {
        if ($expected !== null) {
            $msg = sprintf(
                'Syntax error, unexpected %s, expecting %s',
                $token->getName(),
                Token::getNameFromType($expected)
            );
        } else {
            $msg = sprintf(
                'Syntax error, unexpected %s',
                $token->getName()
            );
        }

        $this->addError($msg, $token->getAttributes());
    }

    public function last(): Token
    {
        if ($this->last === null) {
            throw new \LogicException("Can't call ParserState::last() before first token");
        }

        return $this->last;
    }

    public function addError(string $message, array $attributes = []): void
    {
        $this->errorHandler->handleError(new Error($message, $attributes));
    }

    public function setAttributes(Node $node, $start, $end): void
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

        if (/*$start instanceof Token && */isset($startAttrs['comments'])) {
            $node->setAttribute('comments', $startAttrs['comments']);
        }
    }

    public function getExpressionParser(): ExpressionParserInterface
    {
        return $this->expressionParser;
    }

    public function getStatementParser(): StatementParserInterface
    {
        return $this->statementParser;
    }

    private function handleHaltCompiler(Token $token)
    {
        try {
            $rest = $this->lexer->handleHaltCompiler();
            $token->startAttributes['rest'] = $rest;
        } catch (Error $e) {
            $this->errorHandler->handleError($e);
        }
    }
}
