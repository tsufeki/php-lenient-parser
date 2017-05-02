<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpLenientParser\Statement\ParameterList;
use PhpLenientParser\Statement\Type;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Closure extends AbstractPrefix
{
    /**
     * @var Type
     */
    private $typeParser;

    /**
     * @var ParameterList
     */
    private $parametersParser;

    /**
     * @var Variable
     */
    private $variableParser;

    public function __construct(int $token, Type $typeParser, ParameterList $parametersParser, Variable $variableParser)
    {
        parent::__construct($token);
        $this->typeParser = $typeParser;
        $this->parametersParser = $parametersParser;
        $this->variableParser = $variableParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        if (!$this->isClosure($parser)) {
            return null;
        }

        $token = $parser->lookAhead();
        $static = $parser->eat(Tokens::T_STATIC) !== null;
        $parser->assert(Tokens::T_FUNCTION);
        $ref = $parser->eat(ord('&')) !== null;

        $params = [];
        if ($parser->lookAhead()->type === ord('(')) {
            $params = $this->parametersParser->parse($parser);
        }

        $uses = [];
        if ($parser->lookAhead()->type === Tokens::T_USE) {
            $uses = $this->parseUses($parser);
        }

        $returnType = null;
        if ($parser->eat(ord(':')) !== null) {
            $returnType = $this->typeParser->parse($parser);
        }

        $stmts = [];
        if ($parser->lookAhead()->type === ord('{')) {
            $stmts = $parser->getStatementParser()->parse($parser);
        }

        return $parser->setAttributes(new Node\Expr\Closure(
            [
                'static' => $static,
                'byRef' => $ref,
                'params' => $params,
                'uses' => $uses,
                'returnType' => $returnType,
                'stmts' => $stmts,
            ]
        ), $token, $parser->last());
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return bool
     */
    private function isClosure(ParserStateInterface $parser): bool
    {
        $i = 0;
        if ($parser->lookAhead($i)->type === Tokens::T_STATIC) {
            $i++;
        }
        if ($parser->lookAhead($i)->type !== Tokens::T_FUNCTION) {
            return false;
        }
        $i++;
        if ($parser->lookAhead($i)->type === ord('&')) {
            $i++;
        }
        if ($parser->lookAhead($i)->type !== ord('(')) {
            return false;
        }

        return true;
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Expr\ClosureUse[]
     */
    private function parseUses(ParserStateInterface $parser): array
    {
        $parser->eat();
        $parser->assert(ord('('));
        $uses = [];

        while (true) {
            $first = $parser->lookAhead();
            $ref = $parser->eat(ord('&')) !== null;
            if ($parser->lookAhead()->type !== $this->variableParser->getToken()) {
                break;
            }
            $var = $this->variableParser->parse($parser);
            $uses[] = $parser->setAttributes(new Node\Expr\ClosureUse($var, $ref), $first, $parser->last());
            $parser->eat(ord(','));
        }

        $parser->assert(ord(')'));

        return $uses;
    }
}
