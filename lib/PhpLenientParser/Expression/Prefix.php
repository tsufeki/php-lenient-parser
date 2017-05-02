<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;

class Prefix extends AbstractOperator implements PrefixInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $expr = $parser->getExpressionParser()->parseOrError($parser, $this->getPrecedence());

        $class = $this->getNodeClass();

        return $parser->setAttributes(new $class($expr), $token, $expr);
    }
}
