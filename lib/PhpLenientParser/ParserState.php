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
     * @var Token[]
     */
    private $lookAheadQueue = [];

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
    }

    public function getOption(string $option)
    {
        return $this->options[$option] ?? null;
    }

    public function lookAhead(int $count = 0): Token
    {
        $toRead = $count + 1 - count($this->lookAheadQueue);
        for ($i = 0; $i < $toRead; $i++) {
            $token = new Token();
            $token->type = $this->lexer->getNextToken($token->value, $token->startAttributes, $token->endAttributes);
            if ($token->type === Tokens::T_HALT_COMPILER) {
                $this->handleHaltCompiler($token);
            }
            $this->lookAheadQueue[] = $token;
        }

        return $this->lookAheadQueue[$count];
    }

    public function isNext(int ...$tokenTypes): bool
    {
        return in_array($this->lookAhead()->type, $tokenTypes);
    }

    public function eat(): Token
    {
        $this->last = $this->lookAhead();
        array_shift($this->lookAheadQueue);

        return $this->last;
    }

    public function eatIf(int $tokenType = null): ?Token
    {
        $token = $this->lookAhead();

        if ($tokenType !== null && $tokenType !== $token->type) {
            return null;
        }

        $this->last = $token;
        array_shift($this->lookAheadQueue);

        return $token;
    }

    public function assert(int $tokenType): bool
    {
        $token = $this->lookAhead();

        if ($tokenType !== $token->type) {
            $this->unexpected($token, $tokenType);

            return false;
        }

        $this->last = $token;
        array_shift($this->lookAheadQueue);

        return true;
    }

    public function unexpected(Token $token, int ...$expected): void
    {
        if ($expected !== []) {
            $msg = sprintf(
                'Syntax error, unexpected %s, expecting %s',
                $token->getName(),
                implode(' or ', array_map(function (int $e) {
                    return Token::getNameFromType($e);
                }, $expected))
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
        $attrs = $node->getAttributes();

        foreach (['startLine', 'startTokenPos', 'startFilePos'] as $attr) {
            if (isset($startAttrs[$attr])) {
                $attrs[$attr] = $startAttrs[$attr];
            }
        }

        foreach (['endLine', 'endTokenPos', 'endFilePos'] as $attr) {
            if (isset($endAttrs[$attr])) {
                $attrs[$attr] = $endAttrs[$attr];
            }
        }

        if (/*$start instanceof Token && */isset($startAttrs['comments'])) {
            $attrs['comments'] = $startAttrs['comments'];
        }

        $node->setAttributes($attrs);
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
