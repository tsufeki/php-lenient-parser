<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Method implements StatementInterface
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
        if (!$this->identifierParser->isIdentifierToken($parser->lookAhead(1)->type)
                && ($parser->lookAhead(1)->type !== ord('&')
                    || !$this->identifierParser->isIdentifierToken($parser->lookAhead(2)->type))) {
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

        if ($parser->eatIf(ord('{')) !== null) {
            $stmts = $parser->getStatementParser()->parseList($parser, ord('}'));
            $parser->assert(ord('}'));
        } elseif ($parser->eatIf(ord(';')) !== null) {
            $stmts = null;
        } else {
            $stmts = null;
            $parser->unexpected($parser->lookAhead(), ord('{'), ord(';'));
        }

        $node = new Node\Stmt\ClassMethod($id, [
            'flags' => 0,
            'byRef' => $ref,
            'params' => $params,
            'returnType' => $returnType,
            'stmts' => $stmts,
        ]);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }

    public function getToken(): ?int
    {
        return Tokens::T_FUNCTION;
    }
}
