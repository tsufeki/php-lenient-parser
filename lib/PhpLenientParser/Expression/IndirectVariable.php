<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Expr;
use PhpParser\Parser\Tokens;

class IndirectVariable extends AbstractPrefix
{
    /**
     * @var Variable
     */
    private $variableParser;

    /**
     * @param int $token
     * @param Variable $variableParser
     */
    public function __construct(int $token, Variable $variableParser)
    {
        parent::__construct($token);
        $this->variableParser = $variableParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        switch ($parser->lookAhead()->type) {
            case ord('{'):
                $parser->eat();
                $name = $parser->getExpressionParser()->parseOrError($parser);
                $parser->assert(ord('}'));
                break;
            case $this->variableParser->getToken():
                $name = $this->variableParser->parse($parser);
                break;
            case $this->getToken():
                $name = $this->parse($parser);
                break;
            default:
                $name = $parser->getExpressionParser()->makeErrorNode($token);
        }

        return $parser->setAttributes(new Expr\Variable($name), $token, $name);
    }
}
