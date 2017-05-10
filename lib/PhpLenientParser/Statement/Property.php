<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Variable;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Property implements StatementInterface
{
    /**
     * @var int
     */
    private $token;

    /**
     * @var Variable
     */
    private $variableParser;

    /**
     * @param int      $token
     * @param Variable $variableParser
     */
    public function __construct(int $token, Variable $variableParser)
    {
        $this->token = $token;
        $this->variableParser = $variableParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->lookAhead();
        $parser->eat(Tokens::T_VAR);
        $props = [];

        while (($first = $parser->lookAhead())->type === $this->variableParser->getToken()) {
            $var = $this->variableParser->parseIdentifier($parser);
            $expr = null;
            if ($parser->eat(ord('=')) !== null) {
                $expr = $parser->getExpressionParser()->parseOrError($parser);
            }
            $props[] = $parser->setAttributes(new Node\Stmt\PropertyProperty($var, $expr), $first, $parser->last());
            if ($parser->lookAhead()->type === ord(';') || $parser->assert(ord(',')) === null) {
                break;
            }
        }

        $parser->assert(ord(';'));

        return $parser->setAttributes(new Node\Stmt\Property(0, $props), $token, $parser->last());
    }

    public function getToken()
    {
        return $this->token;
    }
}
