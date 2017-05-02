<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class FunctionCall extends AbstractOperator implements InfixInterface
{
    /**
     * @var ArgumentList
     */
    private $argsParser;

    /**
     * @param int          $token
     * @param int          $precedence
     * @param ArgumentList $argsParser
     */
    public function __construct(int $token, int $precedence, ArgumentList $argsParser)
    {
        parent::__construct($token, $precedence, null);
        $this->argsParser = $argsParser;
    }

    public function parse(ParserStateInterface $parser, Node $left)
    {
        $args = $this->argsParser->parse($parser);
        $node = new Node\Expr\FuncCall($left, $args);

        return $parser->setAttributes($node, $left, $parser->last());
    }
}
