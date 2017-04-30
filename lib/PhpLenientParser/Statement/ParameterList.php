<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;
use PhpLenientParser\Expression\Name;
use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\Expression\Variable;

class ParameterList
{
    /**
     * @var Type
     */
    private $typeParser;

    /**
     * @var Variable
     */
    private $variableParser;

    /**
     * @param Type $typeParser
     * @param Variable $variableParser
     */
    public function __construct(Type $typeParser, Variable $variableParser)
    {
        $this->typeParser = $typeParser;
        $this->variableParser = $variableParser;
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Param[]
     */
    public function parse(ParserStateInterface $parser)
    {
        $parser->eat();
        $params = [];

        while ($parser->lookAhead()->type !== ord(')')) {
            $first = $parser->lookAhead();

            $type = $this->typeParser->parse($parser);
            $ref = $parser->eat(ord('&')) !== null;
            $variadic = $parser->eat(Tokens::T_ELLIPSIS) !== null;

            $var = null;
            $varLast = $parser->last();
            if ($parser->lookAhead()->type === $this->variableParser->getToken()) {
                $var = $this->variableParser->parse($parser);
            }

            $expr = null;
            if ($parser->eat(ord('=')) !== null) {
                $expr = $parser->getExpressionParser()->parseOrError($parser);
            }

            if ($var === null && ($type !== null || $expr !== null || $ref || $variadic)) {
                $errorNode = $parser->getExpressionParser()->makeErrorNode($varLast);
                $var = $parser->setAttributes(new Node\Expr\Variable($errorNode), $errorNode, $errorNode);
            }

            if ($var !== null) {
                $params[] = $parser->setAttributes(
                    new Node\Param($var, $expr, $type, $ref, $variadic),
                    $first, $parser->last()
                );
            }

            if ($parser->eat(ord(',')) === null) {
                break;
            }
        }

        $parser->assert(ord(')'));
        return $params;
    }
}
