<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Function_ implements StatementInterface
{
    /**
     * @var ParameterList
     */
    private $parametersParser;

    /**
     * @var Type
     */
    private $typeParser;

    /**
     * @var Identifier
     */
    private $identifierParser;

    /**
     * @param ParameterList $parametersParser
     * @param Type          $typeParser
     * @param Identifier    $identifierParser
     */
    public function __construct(ParameterList $parametersParser, Type $typeParser, Identifier $identifierParser)
    {
        $this->parametersParser = $parametersParser;
        $this->typeParser = $typeParser;
        $this->identifierParser = $identifierParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        if ($parser->lookAhead(1)->type !== Tokens::T_STRING
                && ($parser->lookAhead(1)->type !== ord('&')
                    || $parser->lookAhead(2)->type !== Tokens::T_STRING)) {
            return null; // Looks like a closure.
        }

        $token = $parser->eat();
        $ref = $parser->eat(ord('&')) !== null;
        $id = $this->identifierParser->parse($parser);

        $params = [];
        if ($parser->isNext(ord('('))) {
            $params = $this->parametersParser->parse($parser);
        }

        $returnType = null;
        if ($parser->eat(ord(':')) !== null) {
            $returnType = $this->typeParser->parse($parser);
        }

        $stmts = [];
        if ($parser->isNext(ord('{'))) {
            $stmts = $parser->getStatementParser()->parse($parser);
        }

        return $parser->setAttributes(new Node\Stmt\Function_(
            $id,
            [
                'byRef' => $ref,
                'params' => $params,
                'returnType' => $returnType,
                'stmts' => $stmts,
            ]
        ), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_FUNCTION;
    }
}
