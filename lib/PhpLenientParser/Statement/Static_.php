<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Variable;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Static_ implements StatementInterface
{
    /**
     * @var Variable
     */
    private $variableParser;

    /**
     * @param Variable $variableParser
     */
    public function __construct(Variable $variableParser)
    {
        $this->variableParser = $variableParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        if ($parser->lookAhead(1)->type !== $this->variableParser->getToken()) {
            return null;
        }

        $token = $parser->eat();
        $vars = [];

        while ($parser->lookAhead()->type === $this->variableParser->getToken()) {
            $var = $this->variableParser->parse($parser);
            $expr = null;
            if ($parser->eat(ord('=')) !== null) {
                $expr = $parser->getExpressionParser()->parseOrError($parser);
            }
            $vars[] = $parser->setAttributes(
                new Node\Stmt\StaticVar($parser->getOption('v3compat') ? $var->name : $var, $expr),
                $var, $parser->last()
            );
            if ($parser->lookAhead()->type === ord(';') || $parser->assert(ord(',')) === null) {
                break;
            }
        }

        $parser->assert(ord(';'));

        return $parser->setAttributes(new Node\Stmt\Static_($vars), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_STATIC;
    }
}
