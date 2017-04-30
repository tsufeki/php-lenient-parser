<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Scope extends AbstractOperator implements InfixInterface
{
    /**
     * @var Identifier
     */
    private $identifierParser;

    /**
     * @var Variable
     */
    private $variableParser;

    /**
     * @var IndirectVariable
     */
    private $indirectVariableParser;

    /**
     * @var ArgumentList
     */
    private $argsParser;

    /**
     * @param int $token
     * @param int $precedence
     * @param Identifier $identifierParser
     * @param Variable $variableParser
     * @param IndirectVariable $indirectVariableParser
     * @param ArgumentList $argsParser
     */
    public function __construct(
        $token,
        $precedence,
        $identifierParser,
        $variableParser,
        $indirectVariableParser,
        $argsParser
    ) {
        parent::__construct($token, $precedence, null);
        $this->identifierParser = $identifierParser;
        $this->variableParser = $variableParser;
        $this->indirectVariableParser = $indirectVariableParser;
        $this->argsParser = $argsParser;
    }

    public function parse(ParserStateInterface $parser, Node $left)
    {
        $parser->eat();
        /** @var Node\Identifier|null */
        $id = null;
        /** @var Node\Expr\Variable|null */
        $var = null;
        /** @var Node\Expr|null */
        $expr = null;

        switch ($parser->lookAhead()->type) {
            case $this->variableParser->getToken():
                $var = $this->variableParser->parse($parser);
                break;
            case $this->indirectVariableParser->getToken():
                $var = $this->indirectVariableParser->parse($parser);
                break;
            case ord('{'):
                $parser->eat();
                $expr = $parser->getExpressionParser()->parseOrError($parser);
                $parser->assert(ord('}'));
                break;
            default:
                $id = $this->identifierParser->parse($parser);
        }

        if ($parser->lookAhead()->type === ord('(')) {
            $args = $this->argsParser->parse($parser);
            $name = $id ?: ($var ?: ($expr ?: $parser->getExpressionParser()->makeErrorNode($parser->last())));
            $node = new Node\Expr\StaticCall($left, $name, $args);
        } elseif ($expr !== null) {
            $node = new Node\Expr\StaticCall($left, $expr, []);
        } elseif ($var !== null) {
            $name = $var->name;
            if (is_string($name)) {
                $name = new Node\VarLikeIdentifier($name);
            }
            $node = new Node\Expr\StaticPropertyFetch($left, $name);
        } else {
            $name = $id ?: $parser->getExpressionParser()->makeErrorNode($parser->last());
            $node = new Node\Expr\ClassConstFetch($left, $name);
        }

        return $parser->setAttributes($node, $left, $parser->last());
    }
}
