<?php declare(strict_types=1);

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
        $ref = $parser->eatIf(ord('&')) !== null;
        $id = $this->identifierParser->parse($parser);
        assert($id !== null);

        $params = [];
        if ($parser->isNext(ord('('))) {
            $params = $this->parametersParser->parse($parser);
        }

        $returnType = null;
        if ($parser->eatIf(ord(':')) !== null) {
            $returnType = $this->typeParser->parse($parser);
        }

        $stmts = [];
        if ($parser->assert(ord('{'))) {
            $stmts = $parser->getStatementParser()->parseList($parser, ord('}'));
            $parser->assert(ord('}'));
        }

        return new Node\Stmt\Function_($id, [
            'byRef' => $ref,
            'params' => $params,
            'returnType' => $returnType,
            'stmts' => $stmts,
        ], $parser->getAttributes($token, $parser->last()));
    }

    public function getToken(): ?int
    {
        return Tokens::T_FUNCTION;
    }
}
