<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class ScopeNew extends AbstractOperator implements InfixInterface
{
    /**
     * @var Variable
     */
    private $variableParser;

    /**
     * @var IndirectVariable
     */
    private $indirectVariableParser;

    /**
     * @param int              $token
     * @param int              $precedence
     * @param Variable         $variableParser
     * @param IndirectVariable $indirectVariableParser
     */
    public function __construct(
        int $token,
        int $precedence,
        Variable $variableParser,
        IndirectVariable $indirectVariableParser
    ) {
        parent::__construct($token, $precedence, null);
        $this->variableParser = $variableParser;
        $this->indirectVariableParser = $indirectVariableParser;
    }

    public function parse(ParserStateInterface $parser, Node $left)
    {
        $parser->eat();

        switch ($parser->lookAhead()->type) {
            case $this->variableParser->getToken():
                /** @var Node\Expr\Variable */
                $var = $this->variableParser->parse($parser);
                $name = $var->name;
                break;
            case $this->indirectVariableParser->getToken():
                /** @var Node\Expr\Variable */
                $var = $this->indirectVariableParser->parse($parser);
                $name = $var->name;
                break;
            default:
                $name = $parser->getExpressionParser()->makeErrorNode($parser->last());
        }

        if (is_string($name) && !$parser->getOption('v3compat')) {
            $name = new Node\VarLikeIdentifier($name);
        }
        $node = new Node\Expr\StaticPropertyFetch($left, $name);

        return $parser->setAttributes($node, $left, $parser->last());
    }
}
